# Filament Memory Tracker

[![Latest Stable Version](http://poser.pugx.org/danilopolani/filament-memory-tracker/v)](https://packagist.org/packages/danilopolani/filament-memory-tracker)
[![Total Downloads](http://poser.pugx.org/danilopolani/filament-memory-tracker/downloads)](https://packagist.org/packages/danilopolani/filament-memory-tracker)

Track the memory usage of your workers and display them in Filament.

<p align="center"><img src="https://i.imgur.com/Y5gnWfl.png" alt="Filament Worker Memory widget preview"></p>

## Installation

Install the package via composer:

```basgh
composer require danilopolani/filament-memory-tracker
```

Then publish the config of the package and the assets as well:

```shell
php artisan vendor:publish --tag=filament-memory-tracker-config
php artisan vendor:publish --tag=filament-memory-tracker-assets
```

### Upgrade
When upgrading, you should republish the assets:

```shell
php artisan vendor:publish --tag=filament-memory-tracker-assets --force
```

## Configuration

There are a few notable configuration options for the package.

Key | Type | Description
------------ | ------------- | -------------
`cache_store` | String | Define the cache store used to track memory usage. By default it will be your `CACHE_DRIVER` env value.
`trackers` | Array | A list of trackers names to be displayed in the dashboard. They must be the same used in your `MemoryTracker()` instance. See **Usage** below to discover more.
`date_format` | String | The [DateTime format](https://www.php.net/manual/en/datetime.format.php) to display dates.

## Usage

In your Worker create a new `MemoryTracker` instance and then ping the `track()` method every time you want. An example with [ReactPHP Event Loop](https://reactphp.org/event-loop/):

```php
<?php

namespace App\Console\Commands;

use Danilopolani\FilamentMemoryTracker\MemoryTracker;
use Illuminate\Console\Command;
use React\EventLoop\Loop;

class MyWorker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'worker:run';

    /**
     * The memory tracker instance.
     *
     * @var MemoryTracker
     */
    protected MemoryTracker $memoryTracker;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->memoryTracker = new MemoryTracker('Worker');
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        Loop::addPeriodicTimer(5, function () {
            $this->memoryTracker->track(bool $realUsage = false);
        });

        return 0;
    }
}
```

Then don't forget to add your tracker name inside the configuration too:

```php
<?php

return [
    // ...

    'trackers' => [
        'Worker',
    ],

];
```

> **Note**: the `$realUsage` flag is the same as [memory_get_usage()](https://www.php.net/manual/en/function.memory-get-usage.php)

### Track restarts

You can track the latest Worker restart date and memory usage as well! If you're working on a custom Worker, you should intercept the exit signals and then call the `$memoryTracker->trackRestart()` method. Otherwise you can use the Trait provided by the package to achieve that:

1. Include `Danilopolani\FilamentMemoryTracker\Concerns\TracksRestart` inside your class;
2. Call `$this->trackRestartMemory(MemoryTracker $memoryTrackerInstance)` inside your constructor.

> **Note**: The `TracksRestart` requires the extension **`pcntl`** to be enabled.

```php
<?php

namespace App\Console\Commands;

use Danilopolani\FilamentMemoryTracker\MemoryTracker;
use Danilopolani\FilamentMemoryTracker\Concerns\TracksRestart;
use Illuminate\Console\Command;
use React\EventLoop\Loop;

class MyWorker extends Command
{
    use TracksRestart;

    // ...

    public function __construct()
    {
        parent::__construct();

        $this->memoryTracker = new MemoryTracker('Worker');
        $this->trackRestartMemory($this->memoryTracker);
    }

    // ...
}
```

### Laravel Queue

You can track [Laravel Queue](laravel.com/docs/8.x/queues) too by listening some specific events in a provider, for example your `AppServiceProvider`.

```php
<?php

namespace App\Providers;

use Danilopolani\FilamentMemoryTracker\MemoryTracker;
use Filament\Filament;
use Illuminate\Queue\Events\Looping;
use Illuminate\Queue\Events\WorkerStopping;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $memoryTracker = new MemoryTracker('Queue');

        // Track memory usage
        Event::listen(Looping::class, function () use ($memoryTracker) {
            $memoryTracker->track();
        });

        // Track restarts
        Event::listen(WorkerStopping::class, function () use ($memoryTracker) {
            $memoryTracker->trackRestart();
        });
    }
}
```

### Additional notes

- The widget will refresh every 5s automatically;
- By default the widget will be shown full-width if there's more than 1 tracker; otherwise, the widget will be a single block:

<img src="https://i.imgur.com/Gg3whu1.png" alt="Memory Tracker widget single block" width="50%">

## APIs

These are the available methods of the `MemoryTracker` class:

Key | Description
------------ | -------------
`track(): void` | Track the current memory usage for the worker.
`trackRestart(bool $resetPeak = true): void` | Track a restart. If `$resetPeak` is true, the memory peak will be purged as well.
`getHistory(): array` | Get the worker's history of memory usage.
`getPeak(): array|null` | Get the worker's memory peak. Returns `null` if no peak found.
`getLatestRestart(): array|null` | Get the worker's latest restart data. Returns `null` if no restart found.
`purge(): void` | Purge all the data of the current worker.
`purgeHistory(): void` | Purge the track history only of the current worker.
`purgePeak(): void` | Purge the memory peak of the current worker.
`purgeRestart(): void` | Purge the latest restart data of the current worker.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email danilo.polani@gmail.com instead of using the issue tracker.

## Credits

- [Danilo Polani](https://github.com/danilopolani)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
