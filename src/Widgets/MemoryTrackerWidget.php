<?php

namespace Danilopolani\FilamentMemoryTracker\Widgets;

use Danilopolani\FilamentMemoryTracker\MemoryTracker;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class MemoryTrackerWidget extends Widget
{
    public static $view = 'filament-memory-tracker::widgets.memory-tracker';

    public function render()
    {
        $charts = [];

        foreach (Config::get('filament-memory-tracker.trackers', []) as $trackerName) {
            $memoryTracker = new MemoryTracker($trackerName);

            $history = $memoryTracker->getHistory();

            // X axis
            $labels = array_map(
                fn (string $date) => (new Carbon($date))->format('H:i:s'),
                array_column($history, 'date')
            );

            // Y axis
            $data = array_map(
                fn ($value) => number_format($value / 1024 / 1024, 3),
                array_column($history, 'value')
            );

            $charts[] = [
                'name' => $trackerName,
                'key' => Str::slug($trackerName),
                'latestRestart' => $memoryTracker->getLatestRestart(),
                'peak' => $memoryTracker->getPeak(),
                'timeseries' => [
                    'labels' => $labels,
                    'data' => $data,
                    'max' => Collection::make($data)->max() + 10,
                ],
            ];
        }

        $this->emit('memoryTrackerUpdated', $charts);

        return view(static::$view, [
            'charts' => $charts,
        ]);
    }
}
