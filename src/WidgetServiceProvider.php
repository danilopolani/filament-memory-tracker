<?php

namespace DaniloPolani\FilamentMemoryTracker;

use DaniloPolani\FilamentMemoryTracker\Widgets\MemoryTrackerWidget;
use Filament\FilamentManager;
use Filament\PluginServiceProvider;
use Spatie\LaravelPackageTools\Package;

class WidgetServiceProvider extends PluginServiceProvider
{
    public static string $name = 'filament-memory-tracker';

    /**
     * {@inheritDoc}
     */
    public function configurePackage(Package $package): void
    {
        parent::configurePackage($package);

        $package
            ->hasAssets()
            ->hasConfigFile();
    }

    /**
     * {@inheritDoc}
     */
    public function packageRegistered(): void
    {
        $this->app->singletonIf('filament', fn (): FilamentManager => new FilamentManager());

        parent::packageRegistered();
    }

    /**
     * {@inheritDoc}
     */
    protected function getWidgets(): array
    {
        return [
            MemoryTrackerWidget::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getStyles(): array
    {
        return [
            self::$name . '-styles' => asset('/vendor/' . self::$name . '/memory-tracker.css'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getScripts(): array
    {
        return [
            self::$name . '-scripts' => asset('/vendor/' . self::$name . '/memory-tracker.js'),
        ];
    }
}
