<?php

namespace Tests\Feature;

use Atldays\Geo\Contracts\GeoContract;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class RequestMacrosTest extends TestCase
{
    public function test_real_client_ip_macro_uses_public_forwarded_ip(): void
    {
        $request = Request::create('/', 'GET', server: [
            'HTTP_X_FORWARDED_FOR' => '10.0.0.1, 8.8.8.8',
            'REMOTE_ADDR' => '127.0.0.1',
        ]);

        $this->assertSame('8.8.8.8', $request->realIp());
    }

    public function test_fake_client_ip_macro_uses_configured_input_key(): void
    {
        Config::set('app.debug', true);
        Config::set('geo.request.fake_ip_key', 'client_ip');

        $request = Request::create('/?client_ip=1.1.1.1', 'GET');

        $this->assertSame('1.1.1.1', $request->fakeIp());
    }

    public function test_geo_macro_can_resolve_geo_data_from_request(): void
    {
        $this->skipIfDatabaseIsMissing();

        Config::set('app.debug', true);
        Config::set('geo.request.fake_ip_key', 'client_ip');

        $request = Request::create('/?client_ip=149.50.244.3', 'GET');
        $result = $request->geo();

        $this->assertInstanceOf(GeoContract::class, $result);
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
