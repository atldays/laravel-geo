<?php

namespace Tests;

use Atldays\Geo\GeoServiceProvider;
use Dotenv\Dotenv;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelData\LaravelDataServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelDataServiceProvider::class,
            GeoServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $basePath = dirname(__DIR__);

        if (file_exists($basePath . '/.env')) {
            Dotenv::createMutable($basePath)->safeLoad();
        }

        $app['config']->set('geo.maxmind.account_id', env('MAXMIND_ACCOUNT_ID'));
        $app['config']->set('geo.maxmind.license_key', env('MAXMIND_LICENSE_KEY'));
        $app['config']->set('geo.maxmind.edition_id', env('MAXMIND_EDITION_ID', 'GeoLite2-City'));
        $app['config']->set('geo.maxmind.download_url', env('MAXMIND_DOWNLOAD_URL'));
        $app['config']->set(
            'geo.maxmind.database_path',
            env('MAXMIND_DATABASE_PATH', $basePath . '/storage/app/testing/geo/maxmind'),
        );
        $app['config']->set('geo.maxmind.database_filename', env('MAXMIND_DATABASE_FILENAME'));
        $app['config']->set('geo.maxmind.metadata_filename', env('MAXMIND_METADATA_FILENAME', 'maxmind-download.json'));
    }

    protected function tearDown(): void
    {
        restore_error_handler();
        restore_exception_handler();

        parent::tearDown();
    }
}
