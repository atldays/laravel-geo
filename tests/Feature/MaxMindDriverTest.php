<?php

namespace Tests\Feature;

use Atldays\Geo\Data\MaxMindConfig;
use Atldays\Geo\Drivers\MaxMind;
use Atldays\Geo\Updaters\MaxMindUpdater;
use Illuminate\Support\Collection;
use Tests\TestCase;

class MaxMindDriverTest extends TestCase
{
    public function test_maxmind_driver_can_resolve_full_city_record(): void
    {
        $driver = $this->makeDriver([
            '149.50.244.3' => [
                'continent' => [
                    'geoname_id' => 6255147,
                    'code' => 'AS',
                    'names' => ['en' => 'Asia'],
                ],
                'country' => [
                    'geoname_id' => 298795,
                    'iso_code' => 'TR',
                    'names' => ['en' => 'Turkey'],
                ],
                'registered_country' => [
                    'geoname_id' => 298795,
                    'iso_code' => 'TR',
                    'names' => ['en' => 'Turkey'],
                ],
                'city' => [
                    'geoname_id' => 745044,
                    'names' => ['en' => 'Istanbul'],
                ],
                'subdivisions' => [
                    [
                        'geoname_id' => 745042,
                        'iso_code' => '34',
                        'names' => ['en' => 'Istanbul'],
                    ],
                ],
                'location' => [
                    'accuracy_radius' => 20,
                    'latitude' => 41.0138,
                    'longitude' => 28.9497,
                    'time_zone' => 'Europe/Istanbul',
                ],
                'postal' => [
                    'code' => '34000',
                ],
            ],
        ]);

        $result = $driver->resolve('149.50.244.3');

        $this->assertSame('149.50.244.3', $result->ip());
        $this->assertSame('AS', $result->continent()?->getCode());
        $this->assertSame('Turkey', $result->country()?->getName());
        $this->assertSame('TR', $result->country()?->getIsoCode());
        $this->assertSame('Istanbul', $result->city()?->getName());
        $this->assertInstanceOf(Collection::class, $result->city()?->getSubdivisions());
        $this->assertCount(1, $result->city()?->getSubdivisions() ?? []);
        $this->assertSame('34', $result->city()?->getSubdivisions()->first()?->getIsoCode());
        $this->assertSame('TR', $result->registeredCountry()?->getIsoCode());
        $this->assertSame(20, $result->accuracyRadius());
        $this->assertSame(41.0138, $result->latitude());
        $this->assertSame(28.9497, $result->longitude());
        $this->assertSame('Europe/Istanbul', $result->timeZone());
        $this->assertSame('34000', $result->postalCode());
        $this->assertNotEmpty($result->data());
    }

    public function test_maxmind_driver_can_resolve_country_only_record(): void
    {
        $driver = $this->makeDriver([
            '8.8.8.8' => [
                'continent' => [
                    'geoname_id' => 6255149,
                    'code' => 'NA',
                    'names' => ['en' => 'North America'],
                ],
                'country' => [
                    'geoname_id' => 6252001,
                    'iso_code' => 'US',
                    'names' => ['en' => 'United States'],
                ],
                'registered_country' => [
                    'geoname_id' => 6252001,
                    'iso_code' => 'US',
                    'names' => ['en' => 'United States'],
                ],
            ],
        ]);

        $result = $driver->resolve('8.8.8.8');

        $this->assertSame('8.8.8.8', $result->ip());
        $this->assertSame('NA', $result->continent()?->getCode());
        $this->assertSame('US', $result->country()?->getIsoCode());
        $this->assertNull($result->city());
        $this->assertSame('US', $result->registeredCountry()?->getIsoCode());
        $this->assertNull($result->accuracyRadius());
        $this->assertNull($result->latitude());
        $this->assertNull($result->longitude());
        $this->assertNull($result->timeZone());
        $this->assertNull($result->postalCode());
    }

    public function test_maxmind_driver_handles_partial_city_without_registered_country(): void
    {
        $driver = $this->makeDriver([
            '203.0.113.10' => [
                'continent' => [
                    'geoname_id' => 6255151,
                    'code' => 'OC',
                    'names' => ['en' => 'Oceania'],
                ],
                'country' => [
                    'geoname_id' => 2077456,
                    'iso_code' => 'AU',
                    'names' => ['en' => 'Australia'],
                ],
                'city' => [
                    'geoname_id' => 2147714,
                    'names' => ['en' => 'Sydney'],
                ],
                'location' => [
                    'latitude' => -33.8688,
                    'longitude' => 151.2093,
                    'time_zone' => 'Australia/Sydney',
                ],
            ],
        ]);

        $result = $driver->resolve('203.0.113.10');

        $this->assertSame('AU', $result->country()?->getIsoCode());
        $this->assertSame('Sydney', $result->city()?->getName());
        $this->assertCount(0, $result->city()?->getSubdivisions() ?? []);
        $this->assertNull($result->registeredCountry());
        $this->assertNull($result->accuracyRadius());
        $this->assertSame(-33.8688, $result->latitude());
        $this->assertSame(151.2093, $result->longitude());
        $this->assertSame('Australia/Sydney', $result->timeZone());
        $this->assertNull($result->postalCode());
    }

    public function test_maxmind_driver_returns_unresolved_result_for_unknown_ip(): void
    {
        $driver = $this->makeDriver([]);

        $result = $driver->resolve('192.0.2.10');

        $this->assertSame('192.0.2.10', $result->ip());
        $this->assertNull($result->continent());
        $this->assertNull($result->country());
        $this->assertNull($result->city());
        $this->assertSame([], $result->data());
    }

    protected function makeDriver(array $records): MaxMind
    {
        return new FakeMaxMind(
            records: $records,
            config: $this->app->make(MaxMindConfig::class),
            updater: $this->app->make(MaxMindUpdater::class),
        );
    }
}

class FakeMaxMind extends MaxMind
{
    public function __construct(
        protected array $records,
        MaxMindConfig $config,
        MaxMindUpdater $updater,
    ) {
        parent::__construct($config, $updater);
    }

    protected function fetch(): array
    {
        return $this->records[(string)$this->ip()] ?? [];
    }
}
