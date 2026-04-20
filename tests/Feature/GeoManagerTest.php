<?php

namespace Tests\Feature;

use Atldays\Geo\Contracts\{CityContract, ContinentContract, CountryContract, GeoContract};
use Atldays\Geo\Drivers\{IpApi, MaxMind};
use Atldays\Geo\GeoManager;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Config, Http};
use Illuminate\Support\Str;
use Tests\TestCase;

class GeoManagerTest extends TestCase
{
    public function test_manager_can_resolve_geo_data_by_ip(): void
    {
        $this->skipIfDatabaseIsMissing();

        $result = $this->app->make(GeoManager::class)->ip('149.50.244.3');

        $this->assertInstanceOf(GeoContract::class, $result);
        $this->assertSame(Str::afterLast((string)config('geo.driver'), '\\'), $result->provider());
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

    public function test_manager_can_fallback_to_ip_api_when_primary_driver_is_unavailable(): void
    {
        Config::set('geo.driver', MaxMind::class);
        Config::set('geo.fallbacks', [IpApi::class]);
        Config::set('geo.maxmind.database_path', __DIR__ . '/missing-maxmind');
        Config::set('geo.ip_api.base_url', 'http://ip-api.test');

        Http::fake([
            'http://ip-api.test/json/8.8.8.8*' => Http::response([
                'status' => 'success',
                'query' => '8.8.8.8',
                'continent' => 'North America',
                'continentCode' => 'NA',
                'country' => 'United States',
                'countryCode' => 'US',
                'city' => 'Mountain View',
                'lat' => 37.422,
                'lon' => -122.084,
                'timezone' => 'America/Los_Angeles',
            ]),
        ]);

        $result = $this->app->make(GeoManager::class)->ip('8.8.8.8');

        $this->assertSame('8.8.8.8', $result->ip());
        $this->assertSame('IpApi', $result->provider());
        $this->assertSame('US', $result->country()?->getIsoCode());
        $this->assertSame('Mountain View', $result->city()?->getName());
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
