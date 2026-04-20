<?php

namespace Tests\Feature;

use Atldays\Geo\Contracts\GeoContract;
use Atldays\Geo\Facades\GeoManager as GeoManagerFacade;
use Atldays\Geo\GeoManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Tests\TestCase;

class GeoManagerFacadeTest extends TestCase
{
    public function test_manager_facade_resolves_geo_manager_singleton(): void
    {
        $this->assertSame(
            $this->app->make(GeoManager::class),
            GeoManagerFacade::getFacadeRoot(),
        );
    }

    public function test_manager_facade_can_resolve_geo_data_by_ip(): void
    {
        $this->skipIfDatabaseIsMissing();

        $result = GeoManagerFacade::ip('149.50.244.3');

        $this->assertInstanceOf(GeoContract::class, $result);
        $this->assertSame('149.50.244.3', $result->ip());
        $this->assertSame(Str::afterLast((string)config('geo.driver'), '\\'), $result->provider());
        $this->assertSame('TR', $result->country()?->getIsoCode());
    }

    public function test_manager_facade_can_resolve_geo_data_from_current_request(): void
    {
        $this->skipIfDatabaseIsMissing();

        Config::set('app.debug', true);
        Config::set('geo.request.fake_ip_key', 'client_ip');

        $request = Request::create('/?client_ip=149.50.244.3', 'GET');
        $this->app->instance('request', $request);

        $result = GeoManagerFacade::request();

        $this->assertSame('149.50.244.3', $result->ip());
        $this->assertSame('TR', $result->country()?->getIsoCode());
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
