<?php

namespace Atldays\Geo\Drivers;

use Atldays\Geo\Contracts\GeoDriver;
use Atldays\Geo\Contracts\GeoResultContract;
use Atldays\Geo\Contracts\Updatable;
use Atldays\Geo\Data\GeoResult;
use Atldays\Geo\Data\MaxMindConfig;
use Atldays\Geo\Data\UpdateOptions;
use Atldays\Geo\Data\UpdateResult;
use Atldays\Geo\Exceptions\DriverUnavailableException;
use Atldays\Geo\Updaters\MaxMindUpdater;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Arr;
use MaxMind\Db\Reader;
use RuntimeException;
use Throwable;

class MaxMind implements GeoDriver, Updatable
{
    protected ?Reader $reader = null;

    public function __construct(
        protected MaxMindConfig $config,
        protected MaxMindUpdater $updater,
    ) {}

    /**
     * @throws ConnectionException
     */
    public function update(UpdateOptions $options): UpdateResult
    {
        return $this->updater->update($options);
    }

    public function locate(string $ip): GeoResultContract
    {
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            throw new RuntimeException(sprintf('The provided IP [%s] is invalid.', $ip));
        }

        try {
            $record = $this->reader()->get($ip);
        } catch (DriverUnavailableException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new DriverUnavailableException(
                'The MaxMind driver is unavailable.',
                previous: $exception,
            );
        }

        $data = is_array($record) ? $record : [];

        if ($data === []) {
            return GeoResult::unresolved($ip);
        }

        $continent = Arr::get($data, 'continent');
        $country = Arr::get($data, 'country');
        $city = Arr::get($data, 'city');
        $registeredCountry = Arr::get($data, 'registered_country');
        $subdivisions = Arr::get($data, 'subdivisions');

        return GeoResult::from([
            'ip' => $ip,
            'continent' => is_array($continent) ? $continent : null,
            'country' => is_array($country) && is_array($continent)
                ? [
                    ...$country,
                    'continent' => $continent,
                ]
                : null,
            'city' => is_array($city) && is_array($country) && is_array($continent)
                ? [
                    ...$city,
                    'country' => [
                        ...$country,
                        'continent' => $continent,
                    ],
                    'subdivisions' => is_array($subdivisions) ? $subdivisions : null,
                ]
                : null,
            'registered_country' => is_array($registeredCountry) && is_array($continent)
                ? [
                    ...$registeredCountry,
                    'continent' => $continent,
                ]
                : null,
            'accuracy_radius' => data_get($data, 'location.accuracy_radius'),
            'latitude' => data_get($data, 'location.latitude'),
            'longitude' => data_get($data, 'location.longitude'),
            'time_zone' => data_get($data, 'location.time_zone'),
            'postal_code' => data_get($data, 'postal.code'),
            'data' => $data,
        ]);
    }

    protected function reader(): Reader
    {
        if ($this->reader instanceof Reader) {
            return $this->reader;
        }

        try {
            return $this->reader = new Reader($this->config->getResolvedDatabasePath());
        } catch (Throwable $exception) {
            throw new DriverUnavailableException(
                'No MaxMind database file could be found in the configured directory.',
                previous: $exception,
            );
        }
    }
}
