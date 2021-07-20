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
    protected string $cacheKey;
    protected int $historyMaxSize;

    /**
     * Create a new tracker instance.
     *
     * @param  string $trackerName
     * @param  int $storedTracksLimit
     * @return void
     */
    public function __construct(string $trackerName = 'default', int $historyMaxSize = 100)
    {
        $this->cache = Cache::store(Config::get('filament-worker-memory-tracker.cache_store'));
        $this->cacheKey = Str::slug($trackerName);
        $this->historyMaxSize = $historyMaxSize;
    }

    /**
     * Track used memory by worker to build the Filament widget.
     *
     * @param  bool $realUsage
     * @return void
     */
    public function track(bool $realUsage = false): void
    {
        $currentlyTracked = $this->cache->get($this->cacheKey, []);
        if (!is_array($currentlyTracked)) {
            $currentlyTracked = [];
        }

        $currentlyTracked[] = [
            'date' => Carbon::now()->toDateTimeString(),
            'value' => memory_get_usage($realUsage),
        ];

        $this->cache->set(
            $this->cacheKey,
            // Slice the last N elements
            array_slice($currentlyTracked, $this->historyMaxSize * -1, $this->historyMaxSize)
        );

        unset($currentlyTracked);
    }

    /**
     * Get history of tracked memory usage.
     *
     * @return array
     */
    public function getHistory(): array
    {
        $result = $this->cache->get($this->cacheKey);

        if (!is_array($result)) {
            $result = [];
        }

        return $result;
    }
}
