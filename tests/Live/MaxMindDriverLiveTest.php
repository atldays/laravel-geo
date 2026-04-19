<?php

namespace Tests\Live;

use Atldays\Geo\Data\UpdateOptions;
use Atldays\Geo\Drivers\MaxMind;
use Atldays\Geo\Exceptions\DriverUnavailableException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MaxMindDriverLiveTest extends TestCase
{
    #[DataProvider('publicIpProvider')]
    public function test_maxmind_driver_can_resolve_known_public_ips(
        string $ip,
        string $countryIsoCode,
        string $continentCode
    ): void {
        if (!env('MAXMIND_ACCOUNT_ID') || !env('MAXMIND_LICENSE_KEY')) {
            $this->markTestSkipped('MaxMind credentials are not configured.');
        }

        /** @var MaxMind $driver */
        $driver = $this->app->make(MaxMind::class);

        try {
            $driver->update(new UpdateOptions);
        } catch (DriverUnavailableException $exception) {
            if (str_contains($exception->getMessage(), '(429)')) {
                $this->markTestSkipped('MaxMind rate-limited the live driver test.');
            }

            throw $exception;
        }

        $result = $driver->resolve($ip);

        $this->assertSame($ip, $result->ip());
        $this->assertSame($countryIsoCode, $result->country()?->getIsoCode());
        $this->assertSame($continentCode, $result->continent()?->getCode());
        $this->assertNotNull($result->country()?->getName());
        $this->assertNotSame([], $result->data());
    }

    public static function publicIpProvider(): array
    {
        return [
            'google-dns' => ['8.8.8.8', 'US', 'NA'],
            'google-dns-secondary' => ['8.8.4.4', 'US', 'NA'],
        ];
    }
}
