<?php

namespace Tests\Feature;

use Atldays\Geo\Contracts\GeoDataContract;
use Atldays\Geo\Data\GeoData;
use Atldays\Geo\Drivers\HttpDriver;
use Atldays\Geo\Exceptions\DriverUnavailableException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HttpDriverTest extends TestCase
{
    public function test_http_driver_returns_json_payload_for_successful_requests(): void
    {
        Http::fake([
            'https://example.com/geo/8.8.8.8' => Http::response([
                'status' => 'success',
                'query' => '8.8.8.8',
            ]),
        ]);

        $driver = new FakeHttpDriver('https://example.com/geo');

        $result = $driver->resolve('8.8.8.8');

        $this->assertSame([
            'status' => 'success',
            'query' => '8.8.8.8',
        ], $result->data());

        Http::assertSent(fn ($request) => $request->url() === 'https://example.com/geo/8.8.8.8');
    }

    public function test_http_driver_wraps_connection_failures_as_driver_unavailable(): void
    {
        Http::fake(function (): never {
            throw new ConnectionException('Connection refused.');
        });

        $driver = new FakeHttpDriver('https://example.com/geo');

        $this->expectException(DriverUnavailableException::class);

        $driver->resolve('8.8.8.8');
    }

    public function test_http_driver_wraps_unsuccessful_http_responses_as_driver_unavailable(): void
    {
        Http::fake([
            '*' => Http::response(['message' => 'Too Many Requests'], 429),
        ]);

        $driver = new FakeHttpDriver('https://example.com/geo');

        $this->expectException(DriverUnavailableException::class);

        $driver->resolve('8.8.8.8');
    }

    public function test_http_driver_wraps_invalid_json_payloads_as_driver_unavailable(): void
    {
        Http::fake([
            '*' => Http::response('{invalid-json', 200),
        ]);

        $driver = new FakeHttpDriver('https://example.com/geo');

        $this->expectException(DriverUnavailableException::class);

        $driver->resolve('8.8.8.8');
    }
}

class FakeHttpDriver extends HttpDriver
{
    public function __construct(
        protected string $baseUrl,
    ) {}

    protected function url(): string
    {
        return $this->baseUrl . '/' . $this->ip();
    }

    protected function result(): GeoDataContract
    {
        return new GeoData(
            ip: (string)$this->ip(),
            data: $this->data(),
        );
    }
}
