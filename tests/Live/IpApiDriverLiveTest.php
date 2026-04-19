<?php

namespace Tests\Live;

use Atldays\Geo\Drivers\IpApi;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class IpApiDriverLiveTest extends TestCase
{
    #[DataProvider('publicIpProvider')]
    public function test_ip_api_driver_can_resolve_known_public_ips(
        string $ip,
        string $countryIsoCode,
        string $continentCode
    ): void {
        $result = $this->app->make(IpApi::class)->resolve($ip);

        $this->assertSame($ip, $result->ip());
        $this->assertSame($countryIsoCode, $result->country()?->getIsoCode());
        $this->assertSame($continentCode, $result->continent()?->getCode());
        $this->assertNotNull($result->latitude());
        $this->assertNotNull($result->longitude());
        $this->assertNotNull($result->timeZone());
        $this->assertNotNull($result->country()?->getName());
        $this->assertNotSame([], $result->data());
    }

    public static function publicIpProvider(): array
    {
        return [
            'google-dns' => ['8.8.8.8', 'US', 'NA'],
            'cloudflare-dns' => ['1.1.1.1', 'AU', 'OC'],
        ];
    }
}
