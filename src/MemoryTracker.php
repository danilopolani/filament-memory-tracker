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

        $currentlyTracked[] = [
            'date' => Carbon::now()->toDateTimeString(),
            'value' => memory_get_usage($this->realUsage),
        ];

        $this->cache->set(
            $this->memoryTrackerKey,
            // Slice the last N elements
            array_slice($currentlyTracked, $this->historyMaxSize * -1, $this->historyMaxSize)
        );

        unset($currentlyTracked);
    }

    /**
     * Track memory usage of the worker in the last restart.
     *
     * @return void
     */
    public function trackRestart(): void
    {
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
     * Purge all the data of the current tracker.
     *
     * @return void
     */
    public function purge(): void
    {
        $this->purgeHistory();
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
     * Purge the tracker restart data.
     *
     * @return void
     */
    public function purgeRestart(): void
    {
        $this->cache->forget($this->memoryTrackerRestartKey);
    }
}
