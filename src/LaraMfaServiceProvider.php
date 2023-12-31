<?php

namespace Kohaku1907\LaraMfa;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Illuminate\Support\Facades\Validator;

class LaraMfaServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('lara-mfa')
            ->hasConfigFile('mfa')
            ->hasTranslations()
            ->hasMigrations(['create_lara_mfa_table', 'create_lara_mfa_settings_table']);
    }

    public function bootingPackage(): void
    {
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('mfa', Http\Middleware\VerifyMultiFactor::class);
        $router->aliasMiddleware('mfa.enforce', Http\Middleware\EnforceMultiFactor::class);
        $router->aliasMiddleware('mfa.require_one', Http\Middleware\RequireAtLeastOneFactor::class);
    }
}
