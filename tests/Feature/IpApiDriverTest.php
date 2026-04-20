<?php

namespace Tests\Feature;

use Atldays\Geo\Data\IpApiConfig;
use Atldays\Geo\Drivers\IpApi;
use Atldays\Geo\Exceptions\DriverUnavailableException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class IpApiDriverTest extends TestCase
{
    public function test_ip_api_driver_requests_expected_endpoint_and_fields(): void
    {
        Http::fake([
            'http://ip-api.test/json/8.8.8.8*' => Http::response([
                'status' => 'success',
                'query' => '8.8.8.8',
                'continent' => 'North America',
                'continentCode' => 'NA',
                'country' => 'United States',
                'countryCode' => 'US',
            ]),
        ]);

        $this->makeDriver()->resolve('8.8.8.8');

        Http::assertSent(function ($request): bool {
            parse_str(parse_url($request->url(), PHP_URL_QUERY) ?? '', $query);

            return $request->method() === 'GET'
                && $request->url() !== ''
                && str_starts_with($request->url(), 'http://ip-api.test/json/8.8.8.8?')
                && ($query['fields'] ?? null) === implode(',', [
                    'status',
                    'message',
                    'query',
                    'continent',
                    'continentCode',
                    'country',
                    'countryCode',
                    'region',
                    'regionName',
                    'city',
                    'zip',
                    'lat',
                    'lon',
                    'timezone',
                ]);
        });
    }

    public function test_ip_api_driver_can_resolve_full_response(): void
    {
        Http::fake([
            'http://ip-api.test/json/8.8.8.8*' => Http::response([
                'status' => 'success',
                'query' => '8.8.8.8',
                'continent' => 'North America',
                'continentCode' => 'NA',
                'country' => 'United States',
                'countryCode' => 'US',
                'region' => 'CA',
                'regionName' => 'California',
                'city' => 'Mountain View',
                'zip' => '94043',
                'lat' => 37.422,
                'lon' => -122.084,
                'timezone' => 'America/Los_Angeles',
            ]),
        ]);

        $result = $this->makeDriver()->resolve('8.8.8.8');

        $this->assertSame('8.8.8.8', $result->ip());
        $this->assertSame('IpApi', $result->provider());
        $this->assertSame('NA', $result->continent()?->getCode());
        $this->assertNull($result->continent()?->getExternalId());
        $this->assertSame('US', $result->country()?->getIsoCode());
        $this->assertNull($result->country()?->getExternalId());
        $this->assertSame('Mountain View', $result->city()?->getName());
        $this->assertNull($result->city()?->getExternalId());
        $this->assertCount(1, $result->city()?->getSubdivisions() ?? []);
        $this->assertSame('CA', $result->city()?->getSubdivisions()->first()?->getIsoCode());
        $this->assertNull($result->city()?->getSubdivisions()->first()?->getExternalId());
        $this->assertNull($result->registeredCountry());
        $this->assertNull($result->accuracyRadius());
        $this->assertSame(37.422, $result->latitude());
        $this->assertSame(-122.084, $result->longitude());
        $this->assertSame('America/Los_Angeles', $result->timeZone());
        $this->assertSame('94043', $result->postalCode());
        $this->assertNotEmpty($result->data());
    }

    public function test_ip_api_driver_handles_partial_success_payload(): void
    {
        Http::fake([
            '*' => Http::response([
                'status' => 'success',
                'query' => '1.1.1.1',
                'country' => 'Australia',
                'countryCode' => 'AU',
                'lat' => -33.494,
                'lon' => 143.2104,
            ]),
        ]);

        $result = $this->makeDriver()->resolve('1.1.1.1');

        $this->assertSame('1.1.1.1', $result->ip());
        $this->assertSame('IpApi', $result->provider());
        $this->assertNull($result->continent());
        $this->assertNull($result->country());
        $this->assertNull($result->city());
        $this->assertSame(-33.494, $result->latitude());
        $this->assertSame(143.2104, $result->longitude());
        $this->assertNull($result->timeZone());
        $this->assertNull($result->postalCode());
        $this->assertSame('Australia', $result->data()['country']);
        $this->assertSame('AU', $result->data()['countryCode']);
    }

    public function test_ip_api_driver_marks_failed_status_as_unavailable(): void
    {
        Http::fake([
            '*' => Http::response([
                'status' => 'fail',
                'message' => 'private range',
                'query' => '127.0.0.1',
            ]),
        ]);

        $this->expectException(DriverUnavailableException::class);
        $this->expectExceptionMessage('private range');

        $this->makeDriver()->resolve('127.0.0.1');
    }

    public function test_ip_api_driver_marks_missing_status_as_unavailable(): void
    {
        Http::fake([
            '*' => Http::response([
                'query' => '8.8.4.4',
                'country' => 'United States',
                'countryCode' => 'US',
            ]),
        ]);

        $this->expectException(DriverUnavailableException::class);
        $this->expectExceptionMessage('did not return a successful lookup result');

        $this->makeDriver()->resolve('8.8.4.4');
    }

    protected function makeDriver(): IpApi
    {
        return new IpApi(
            config: new IpApiConfig(
                baseUrl: 'http://ip-api.test',
                timeout: 5,
            ),
        );
    }
}
