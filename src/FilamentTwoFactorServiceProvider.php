<?php

namespace CherryAnt\FilamentTwoFactor;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Facades\FilamentAsset;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentTwoFactorServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-two-factor';

    public static string $viewNamespace = 'filamenttwofactor';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasRoute("web")
            ->hasViews()
            ->hasTranslations()
            ->hasMigration('add_two_factor_columns_to_table')
            ->hasCommand(Install::class);
    }
}