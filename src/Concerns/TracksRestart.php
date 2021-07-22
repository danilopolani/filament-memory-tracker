<?php

namespace Danilopolani\FilamentMemoryTracker\Concerns;

use Danilopolani\FilamentMemoryTracker\MemoryTracker;

trait TracksRestart
{
    /**
     * Track the memory on worker restart.
     *
     * @param  MemoryTracker $tracker
     * @return void
     */
    protected function trackRestartMemory(MemoryTracker $tracker): void
    {
        if (!extension_loaded('pcntl')) {
            return;
        }

        pcntl_async_signals(true);

        $quitCallback = function (int $sigNo) use ($tracker) {
            $tracker->trackRestart();

            exit($sigNo);
        };

        pcntl_signal(SIGTERM, $quitCallback);
        pcntl_signal(SIGQUIT, $quitCallback);
        pcntl_signal(SIGINT, $quitCallback);
    }
}
