<?php

namespace Tests\Feature;

use Atldays\Geo\Contracts\CityContract;
use Atldays\Geo\Contracts\ContinentContract;
use Atldays\Geo\Contracts\CountryContract;
use Atldays\Geo\Contracts\GeoResultContract;
use Atldays\Geo\GeoManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class GeoManagerTest extends TestCase
{
    public function test_manager_can_resolve_geo_data_by_ip(): void
    {
        $this->skipIfDatabaseIsMissing();

        $result = $this->app->make(GeoManager::class)->ip('149.50.244.3');

        $this->assertInstanceOf(GeoResultContract::class, $result);
        $this->assertInstanceOf(ContinentContract::class, $result->continent());
        $this->assertInstanceOf(CountryContract::class, $result->country());
        $this->assertInstanceOf(CityContract::class, $result->city());
        $this->assertSame('149.50.244.3', $result->ip());
        $this->assertSame('TR', $result->country()?->getIsoCode());
        $this->assertSame('AS', $result->continent()?->getCode());
        $this->assertNotEmpty($result->data());
    }

    public function test_manager_can_resolve_geo_data_from_request(): void
    {
        $this->skipIfDatabaseIsMissing();

        Config::set('app.debug', true);
        Config::set('geo.request.fake_ip_key', 'client_ip');

        $request = Request::create('/?client_ip=149.50.244.3', 'GET');

        $result = $this->app->make(GeoManager::class)->request($request);

        $this->assertSame('149.50.244.3', $result->ip());
        $this->assertSame('TR', $result->country()?->getIsoCode());
    }

    public function test_manager_can_resolve_geo_data_from_current_request(): void
    {
        $this->skipIfDatabaseIsMissing();

        Config::set('app.debug', true);
        Config::set('geo.request.fake_ip_key', 'client_ip');

        $request = Request::create('/?client_ip=149.50.244.3', 'GET');
        $this->app->instance('request', $request);

        $result = $this->app->make(GeoManager::class)->request();

        $this->assertSame('149.50.244.3', $result->ip());
        $this->assertSame('TR', $result->country()?->getIsoCode());
    }

    protected function skipIfDatabaseIsMissing(): void
    {
        $files = $this->app->make(Filesystem::class);
        $databasePath = (string)config('geo.maxmind.database_path');
        $matches = $files->glob($databasePath . DIRECTORY_SEPARATOR . '*.mmdb');

        if (($matches[0] ?? null) === null) {
            $this->markTestSkipped('No downloaded MaxMind database was found.');
        }
    }
}
