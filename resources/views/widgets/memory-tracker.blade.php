<x-filament::card :class="count($charts) > 1 ? 'memory-tracker--col-span-full' : ''">
    <div wire:poll.5s></div>

    <header class="flex items-center justify-between w-full memory-tracker--mb-10">
        <h2 class="text-2xl">
            Memory usage
        </h2>
    </header>

    <div @if (count($charts) > 1) class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-8" @endif>
        @foreach ($charts as $chart)
            <div>
                <p class="memory-tracker-chart-name">{{ $chart['name'] }}</p>

                @if ($chart['latestRestart'])
                    <p class="memory-tracker--mb-2 memory-tracker--text-sm">
                        <strong>🔄 Latest restart</strong>: {{ $chart['latestRestart']['date'] }} (<strong>{{ $chart['latestRestart']['value'] }} MB</strong>)
                    </p>
                @endif

                <div wire:ignore>
                    <canvas id="memory-tracker-chart-{{ $chart['key'] }}" class="memory-tracker-chart" width="100%" height="250"></canvas>
                </div>
            </div>
        @endforeach
    </div>

    <script>const memoryTrackerCharts = @json($charts);</script>
</x-filament::card>
