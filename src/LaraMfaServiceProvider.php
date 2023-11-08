<?php

namespace Kohaku1907\LaraMfa;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Kohaku1907\LaraMfa\Commands\LaraMfaCommand;

class LaraMfaServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('lara-mfa')
            ->hasConfigFile()
            ->hasMigration('create_lara-mfa_table');
    }
}
