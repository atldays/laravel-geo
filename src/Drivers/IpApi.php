<?php

namespace Atldays\Geo\Drivers;

use Atldays\Geo\Contracts\GeoDataContract;
use Atldays\Geo\Data\{GeoData, IpApiConfig};
use Atldays\Geo\Exceptions\DriverUnavailableException;
use Illuminate\Http\Client\PendingRequest;

class IpApi extends HttpDriver
{
    public function __construct(protected IpApiConfig $config) {}

    protected function url(): string
    {
        return rtrim($this->config->baseUrl, '/')
            . '/json/' . rawurlencode($this->ip())
            . '?' . http_build_query([
                'fields' => implode(',', [
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
                ]),
            ]);
    }

    protected function http(): PendingRequest
    {
        $request = parent::http();

        if (is_int($this->config->timeout) && $this->config->timeout > 0) {
            $request = $request->timeout($this->config->timeout);
        }

        return $request;
    }

    protected function fetch(): array
    {
        $payload = parent::fetch();

        if (($payload['status'] ?? null) !== 'success') {
            throw DriverUnavailableException::because(
                static::class,
                $this->dataStringFromPayload($payload, 'message')
                ?? 'The remote IP-API service did not return a successful lookup result.',
            );
        }

        return $payload;
    }

    protected function result(): GeoDataContract
    {
        $continent = $this->continent();
        $country = $this->country($continent);
        $subdivisions = $this->subdivisions();

        return GeoData::from([
            'ip' => $this->ip(),
            'continent' => $continent,
            'country' => $country,
            'city' => $this->city($country, $subdivisions),
            'registered_country' => null,
            'accuracy_radius' => null,
            'latitude' => $this->dataFloat('lat'),
            'longitude' => $this->dataFloat('lon'),
            'time_zone' => $this->dataString('timezone'),
            'postal_code' => $this->dataString('zip'),
            'data' => $this->data(),
        ]);
    }

    protected function continent(): ?array
    {
        $name = $this->dataString('continent');
        $code = $this->dataString('continentCode');

        if ($name === null || $code === null) {
            return null;
        }

        return [
            'name' => $name,
            'code' => $code,
        ];
    }

    protected function country(?array $continent): ?array
    {
        $name = $this->dataString('country');
        $isoCode = $this->dataString('countryCode');

        if ($name === null || $isoCode === null || $continent === null) {
            return null;
        }

        return [
            'name' => $name,
            'iso_code' => $isoCode,
            'continent' => $continent,
        ];
    }

    protected function city(?array $country, ?array $subdivisions): ?array
    {
        $name = $this->dataString('city');

        if ($name === null || $country === null) {
            return null;
        }

        return [
            'name' => $name,
            'country' => $country,
            'subdivisions' => $subdivisions,
        ];
    }

    protected function subdivisions(): ?array
    {
        $isoCode = $this->dataString('region');
        $name = $this->dataString('regionName');

        if ($isoCode === null && $name === null) {
            return null;
        }

        return [[
            'iso_code' => $isoCode ?? $name,
            'name' => $name ?? $isoCode,
        ]];
    }

    protected function dataStringFromPayload(array $payload, string $key): ?string
    {
        $value = $payload[$key] ?? null;

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
