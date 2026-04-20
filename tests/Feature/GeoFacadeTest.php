<?php

namespace Tests\Feature;

use Atldays\Geo\Contracts\GeoContract;
use Atldays\Geo\Facades\Geo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Tests\TestCase;

class GeoFacadeTest extends TestCase
{
    public function test_geo_facade_resolves_current_geo_data_from_container(): void
    {
        $this->skipIfDatabaseIsMissing();

        Config::set('app.debug', true);
        Config::set('geo.request.fake_ip_key', 'client_ip');

        $request = Request::create('/?client_ip=149.50.244.3', 'GET');
        $this->app->instance('request', $request);

        $this->assertInstanceOf(GeoContract::class, Geo::getFacadeRoot());
        $this->assertSame('149.50.244.3', Geo::getFacadeRoot()->ip());
    }

    public function test_geo_contract_can_be_resolved_through_dependency_injection(): void
    {
        $this->skipIfDatabaseIsMissing();

        Config::set('app.debug', true);
        Config::set('geo.request.fake_ip_key', 'client_ip');

        $request = Request::create('/?client_ip=149.50.244.3', 'GET');
        $this->app->instance('request', $request);

        $geo = $this->app->make(GeoContract::class);

        $this->assertInstanceOf(GeoContract::class, $geo);
        $this->assertSame('149.50.244.3', $geo->ip());
    }

    public function test_geo_facade_can_resolve_geo_data_from_current_request(): void
    {
        $this->skipIfDatabaseIsMissing();

        Config::set('app.debug', true);
        Config::set('geo.request.fake_ip_key', 'client_ip');

        $request = Request::create('/?client_ip=149.50.244.3', 'GET');
        $this->app->instance('request', $request);

        $this->assertSame('149.50.244.3', Geo::ip());
        $this->assertSame(Str::afterLast((string)config('geo.driver'), '\\'), Geo::provider());
        $this->assertSame('TR', Geo::country()?->getIsoCode());
        $this->assertSame('AS', Geo::continent()?->getCode());
        $this->assertNotNull(Geo::city());
        $this->assertIsArray(Geo::data());
    }

    protected function skipIfDatabaseIsMissing(): void
    {
        $databasePath = (string)config('geo.maxmind.database_path');
        $matches = glob($databasePath . DIRECTORY_SEPARATOR . '*.mmdb');

        if (($matches[0] ?? null) === null) {
            $this->markTestSkipped('No downloaded MaxMind database was found.');
        }
    }
}
