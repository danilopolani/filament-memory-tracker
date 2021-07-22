<?php

namespace Danilopolani\FilamentMemoryTracker;

use Illuminate\Contracts\Cache\Repository as CacheContract;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class MemoryTracker
{
    protected CacheContract $cache;

    protected string $memoryTrackerKey;

    protected string $memoryTrackerPeakKey;

    protected string $memoryTrackerRestartKey;

    protected int $historyMaxSize;

    protected bool $realUsage;

    /**
     * Create a new tracker instance.
     *
     * @param  string $trackerName
     * @param  int $storedTracksLimit
     * @param  bool $realusage
     * @return void
     */
    public function __construct(string $trackerName = 'default', int $historyMaxSize = 100, bool $realUsage = false)
    {
        $this->cache = Cache::store(Config::get('filament-worker-memory-tracker.cache_store'));
        $this->memoryTrackerKey = 'memory_tracker:' . Str::slug($trackerName);
        $this->memoryTrackerPeakKey = 'memory_tracker_peak:' . Str::slug($trackerName);
        $this->memoryTrackerRestartKey = 'memory_tracker_restart:' . Str::slug($trackerName);
        $this->historyMaxSize = $historyMaxSize;
        $this->realUsage = $realUsage;
    }

    /**
     * Track the current memory usage of the worker.
     *
     * @return void
     */
    public function track(): void
    {
        $currentlyTracked = $this->cache->get($this->memoryTrackerKey, []);
        if (!is_array($currentlyTracked)) {
            $currentlyTracked = [];
        }

        $currentMemory = memory_get_usage($this->realUsage);
        $today = Carbon::now()->toDateTimeString();

        $currentlyTracked[] = [
            'date' => Carbon::now()->toDateTimeString(),
            'value' => $currentMemory,
        ];

        $this->cache->set(
            $this->memoryTrackerKey,
            // Slice the last N elements
            array_slice($currentlyTracked, $this->historyMaxSize * -1, $this->historyMaxSize)
        );

        // Set or update the memory peak
        $currentPeak = $this->cache->get($this->memoryTrackerPeakKey);

        if (is_null($currentPeak) || $currentMemory > $currentPeak['value']) {
            $this->cache->set($this->memoryTrackerPeakKey, [
                'date' => $today,
                'value' => $currentMemory,
            ]);
        }

        unset($currentlyTracked);
        unset($currentMemory);
        unset($today);
        unset($currentPeak);
    }

    /**
     * Track memory usage of the worker in the latest restart.
     *
     * @param  bool $resetPeak
     * @return void
     */
    public function trackRestart(bool $resetPeak = true): void
    {
        if ($resetPeak) {
            $this->purgePeak();
        }

        $this->cache->forever(
            $this->memoryTrackerRestartKey,
            [
                'date' => Carbon::now()->toDateTimeString(),
                'value' => memory_get_usage($this->realUsage),
            ]
        );
    }

    /**
     * Get history of tracked memory usage.
     *
     * @return array
     */
    public function getHistory(): array
    {
        $result = $this->cache->get($this->memoryTrackerKey);

        if (!is_array($result)) {
            $result = [];
        }

        return $result;
    }

    /**
     * Get peak of memory usage.
     *
     * @return array
     */
    public function getPeak(): ?array
    {
        if (!$peak = $this->cache->get($this->memoryTrackerPeakKey)) {
            return null;
        }

        return [
            'date' =>  (new Carbon($peak['date']))->format(
                Config::get('filament-worker-memory-tracker.date_format', 'j F Y @ H:i:s')
            ),
            'value' => number_format($peak['value'] / 1024 / 1024, 3),
        ];
    }

    /**
     * Get data about the latest restart.
     *
     * @return array|null
     */
    public function getLatestRestart(): ?array
    {
        if (!$latestRestart = $this->cache->get($this->memoryTrackerRestartKey)) {
            return null;
        }

        return [
            'date' =>  (new Carbon($latestRestart['date']))->format(
                Config::get('filament-worker-memory-tracker.date_format', 'j F Y @ H:i:s')
            ),
            'value' => number_format($latestRestart['value'] / 1024 / 1024, 3),
        ];
    }

    /**
     * Purge all the data of the current tracker.
     *
     * @return void
     */
    public function purge(): void
    {
        $this->purgeHistory();
        $this->purgePeak();
        $this->purgeRestart();
    }

    /**
     * Purge the tracker history.
     *
     * @return void
     */
    public function purgeHistory(): void
    {
        $this->cache->forget($this->memoryTrackerKey);
    }

    /**
     * Purge the tracker peak.
     *
     * @return void
     */
    public function purgePeak(): void
    {
        $this->cache->forget($this->memoryTrackerPeakKey);
    }

    /**
     * Purge the tracker restart data.
     *
     * @return void
     */
    public function purgeRestart(): void
    {
        $this->cache->forget($this->memoryTrackerRestartKey);
    }
}
