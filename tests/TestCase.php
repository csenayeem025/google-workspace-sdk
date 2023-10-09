<?php

namespace Glamstack\GoogleWorkspace\Tests;

use Glamstack\GoogleWorkspace\ApiClientServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

//        ini_set('memory_limit', '48M');
        if(!is_dir(__DIR__.'/../vendor/orchestra/testbench-core/laravel/storage/keys')){
            mkdir(__DIR__.'/../vendor/orchestra/testbench-core/laravel/storage/keys');
        }
        if (!is_dir(__DIR__.'/../vendor/orchestra/testbench-core/laravel/storage/keys/glamstack-google-workspace')){
            mkdir(__DIR__.'/../vendor/orchestra/testbench-core/laravel/storage/keys/glamstack-google-workspace');
        }
        if (!is_link(__DIR__.'/../vendor/orchestra/testbench-core/laravel/storage/keys/glamstack-google-workspace/test.json')) {
            symlink(__DIR__ . '/../storage/keys/glamstack-google-workspace/test.json', __DIR__.'/../vendor/orchestra/testbench-core/laravel/storage/keys/glamstack-google-workspace/test.json');
        }
        if (!is_link(__DIR__.'/../vendor/orchestra/testbench-core/laravel/storage/keys/glamstack-google-workspace/prod.json')) {
            symlink(__DIR__ . '/../storage/keys/glamstack-google-workspace/prod.json', __DIR__.'/../vendor/orchestra/testbench-core/laravel/storage/keys/glamstack-google-workspace/prod.json');
        }

        if (!is_link(__DIR__.'/../vendor/orchestra/testbench-core/laravel/composer.lock')) {
            symlink(__DIR__ . '/../composer.lock', __DIR__.'/../vendor/orchestra/testbench-core/laravel/composer.lock');
        }
    }

    protected function getPackageProviders($app)
    {
        return [
            ApiClientServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
    }
}
