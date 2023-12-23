<?php

namespace CherryAnt\FilamentTwoFactor;

use CherryAnt\FilamentTwoFactor\Commands\Install;
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
            ->hasRoute('web')
            ->hasViews()
            ->hasTranslations()
            ->hasMigration('create_two_factor_tables')
            ->hasCommand(Install::class);
    }
}
