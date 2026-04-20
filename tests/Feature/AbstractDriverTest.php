<?php

namespace Tests\Feature;

use Atldays\Geo\Contracts\GeoContract;
use Atldays\Geo\Data\Geo;
use Atldays\Geo\Drivers\AbstractDriver;
use Atldays\Geo\Exceptions\DriverException;
use Tests\TestCase;

class AbstractDriverTest extends TestCase
{
    public function test_abstract_driver_rejects_invalid_ip(): void
    {
        $driver = new FakeDriver;

        $this->expectException(DriverException::class);

        $driver->resolve('invalid-ip');
    }

    public function test_abstract_driver_rejects_access_to_ip_before_resolve(): void
    {
        $driver = new FakeDriver;

        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('The driver IP has not been initialized yet.');

        $driver->currentIp();
    }

    public function test_abstract_driver_returns_unresolved_geo_data_for_empty_payload(): void
    {
        $driver = new FakeDriver([
            '127.0.0.1' => [],
        ]);

        $result = $driver->resolve('127.0.0.1');

        $this->assertSame('127.0.0.1', $result->ip());
        $this->assertSame('FakeDriver', $result->provider());
        $this->assertSame([], $result->data());
        $this->assertNull($result->country());
        $this->assertNull($result->city());
    }

    public function test_abstract_driver_replaces_cached_data_on_repeated_resolve_calls(): void
    {
        $driver = new FakeDriver([
            '149.50.244.3' => [
                'country' => [
                    'geoname_id' => 298795,
                    'names' => ['en' => 'Turkey'],
                    'iso_code' => 'TR',
                    'continent' => [
                        'geoname_id' => 6255147,
                        'names' => ['en' => 'Asia'],
                        'code' => 'AS',
                    ],
                ],
                'postal' => ['code' => '34000'],
            ],
            '8.8.8.8' => [
                'country' => [
                    'geoname_id' => 6252001,
                    'names' => ['en' => 'United States'],
                    'iso_code' => 'US',
                    'continent' => [
                        'geoname_id' => 6255149,
                        'names' => ['en' => 'North America'],
                        'code' => 'NA',
                    ],
                ],
            ],
        ]);

        $first = $driver->resolve('149.50.244.3');
        $second = $driver->resolve('8.8.8.8');

        $this->assertSame('TR', $first->country()?->getIsoCode());
        $this->assertSame('34000', $first->postalCode());
        $this->assertSame('US', $second->country()?->getIsoCode());
        $this->assertNull($second->postalCode());
        $this->assertSame('US', $driver->cachedCountryIsoCode());
        $this->assertNull($driver->cachedPostalCode());
    }
}

class FakeDriver extends AbstractDriver
{
    public function __construct(
        protected array $records = [],
    ) {}

    public function cachedCountryIsoCode(): ?string
    {
        return $this->dataString('country.iso_code');
    }

    public function cachedPostalCode(): ?string
    {
        return $this->dataString('postal.code');
    }

    public function currentIp(): string
    {
        return $this->ip();
    }

    protected function fetch(): array
    {
        return $this->records[(string)$this->ip()] ?? [];
    }

    protected function result(): GeoContract
    {
        $country = $this->dataArray('country');

        return Geo::from([
            'ip' => $this->ip(),
            'provider' => $this->provider(),
            'country' => is_array($country)
                ? [
                    'name' => $country['names']['en'] ?? null,
                    'iso_code' => $country['iso_code'] ?? null,
                    'external_id' => $country['geoname_id'] ?? null,
                    'continent' => [
                        'name' => $country['continent']['names']['en'] ?? null,
                        'code' => $country['continent']['code'] ?? null,
                        'external_id' => $country['continent']['geoname_id'] ?? null,
                    ],
                ]
                : null,
            'postal_code' => $this->dataString('postal.code'),
            'data' => $this->data(),
        ]);
    }
}
