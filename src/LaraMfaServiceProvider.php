<?php

namespace Kohaku1907\LaraMfa;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaraMfaServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('lara-mfa')
            ->hasConfigFile('mfa')
            ->hasMigration('create_lara-mfa_table');
    }

    public function bootingPackage(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('mfa', Http\Middleware\VerifyMultiFactor::class);
        $router->aliasMiddleware('mfa.force', Http\Middleware\ForceVerifyMultiFactor::class);
    }
}
