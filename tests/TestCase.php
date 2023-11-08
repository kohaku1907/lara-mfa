<?php

namespace Kohaku1907\LaraMfa\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Kohaku1907\LaraMfa\LaraMfaServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Kohaku1907\\LaraMfa\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            LaraMfaServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_lara-mfa_table.php.stub';
        $migration->up();
        */
    }
}
