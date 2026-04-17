<?php

namespace Tests\Feature;

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

        $this->assertSame('8.8.8.8', $request->realClientIp());
    }

    public function test_fake_client_ip_macro_uses_configured_input_key(): void
    {
        Config::set('app.debug', true);
        Config::set('geo.request.fake_ip_key', 'client_ip');

        $request = Request::create('/?client_ip=1.1.1.1', 'GET');

        $this->assertSame('1.1.1.1', $request->fakeClientIp());
    }
}
