<?php

namespace Danilopolani\FilamentMemoryTracker;

use Danilopolani\FilamentMemoryTracker\Widgets\MemoryTrackerWidget;
use Filament\Filament;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentMemoryTrackerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-memory-tracker')
            ->hasViews()
            ->hasAssets()
            ->hasConfigFile();
    }

    public function bootingPackage()
    {
        Livewire::component(MemoryTrackerWidget::getName(), MemoryTrackerWidget::class);
        Filament::registerWidget(MemoryTrackerWidget::class);

        Filament::serving(function () {
            Filament::registerScript($this->package->name, asset('/vendor/' . $this->package->name . '/memory-tracker.js'));
            Filament::registerStyle($this->package->name, asset('/vendor/' . $this->package->name . '/memory-tracker.css'));
        });
    }
}
