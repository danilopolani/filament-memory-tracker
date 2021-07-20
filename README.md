# Filament Memory Tracker

[![Latest Version on Packagist](https://img.shields.io/packagist/v/danilopolani/filament-memory-tracker.svg?style=flat-square)](https://packagist.org/packages/danilopolani/filament-memory-tracker)
[![Total Downloads](https://img.shields.io/packagist/dt/danilopolani/filament-memory-tracker.svg?style=flat-square)](https://packagist.org/packages/danilopolani/filament-memory-tracker)

Track the memory usage of your workers and display them in Filament.

<p align="center"><img src="https://i.imgur.com/8M2Hzjl.png" alt="Filament Worker Memory widget preview"></p>

## Installation

You can install the package via composer:

```shell
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

**`cache_store`**

Define the cache store used to track memory usage. By default it will be your `CACHE_DRIVER` env value.  

**`trackers`**

A list of trackers names to be displayed in the dashboard. They must be the same used in your `MemoryTracker()` instance. See **Usage** below to discover more.

## Usage

In your Worker create a new `MemoryTracker` and then ping the `track()` method every time you want. An example with [ReactPHP Event Loop](https://reactphp.org/event-loop/):

```php
<?php

namespace App\Console\Commands;

use Danilopolani\FilamentMemoryTracker\MemoryTracker;
use Illuminate\Console\Command;
use React\EventLoop\Loop;

class workerRun extends Command
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

### Track Queue

You can track Laravel Queues too by connecting a listener to the `Looping` event and track inside there the usage:

```php
<?php

namespace App\Providers;

use Danilopolani\FilamentMemoryTracker\MemoryTracker;
use Filament\Filament;
use Illuminate\Queue\Events\Looping;
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

        Event::listen(Looping::class, function () use ($memoryTracker) {
            $memoryTracker->track();
        });
    }
}
```

### Additional notes

By default the widget will be shown full-width if there's more than 1 tracker; otherwise, the widget will be a single block:

![Memory Tracker widget single block](https://i.imgur.com/0zdoMb2.png)

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
