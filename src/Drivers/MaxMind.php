<?php

namespace Atldays\Geo\Drivers;

use Atldays\Geo\Contracts\GeoDataContract;
use Atldays\Geo\Contracts\GeoDriver;
use Atldays\Geo\Contracts\GeoDriverUpdatable;
use Atldays\Geo\Data\GeoData;
use Atldays\Geo\Data\MaxMindConfig;
use Atldays\Geo\Data\UpdateOptions;
use Atldays\Geo\Data\UpdateResult;
use Atldays\Geo\Drivers\Concerns\InteractsWithDriverData;
use Atldays\Geo\Exceptions\DriverException;
use Atldays\Geo\Exceptions\DriverUnavailableException;
use Atldays\Geo\Updaters\MaxMindUpdater;
use Illuminate\Http\Client\ConnectionException;
use MaxMind\Db\Reader;
use Throwable;

class MaxMind implements GeoDriver, GeoDriverUpdatable
{
    use InteractsWithDriverData;

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

    public function resolve(string $ip): GeoDataContract
    {
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            throw DriverException::invalidIp(self::class, $ip);
        }

        try {
            $record = $this->reader()->get($ip);
        } catch (DriverUnavailableException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw DriverUnavailableException::because(
                self::class,
                'The database reader could not process the requested IP.',
                $exception,
            );
        }

        $data = is_array($record) ? $record : [];
        $this->data = $data;

        if ($data === []) {
            return GeoData::unresolved($ip);
        }

        $continent = $this->continent();
        $country = $this->country($continent);

        return GeoData::from([
            'ip' => $ip,
            'continent' => $continent,
            'country' => $country,
            'city' => $this->city($country),
            'registered_country' => $this->registeredCountry($continent),
            'accuracy_radius' => $this->accuracyRadius(),
            'latitude' => $this->latitude(),
            'longitude' => $this->longitude(),
            'time_zone' => $this->timeZone(),
            'postal_code' => $this->postalCode(),
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
            throw DriverUnavailableException::because(
                self::class,
                'No MaxMind database file could be found in the configured directory.',
                $exception,
            );
        }
    }

    protected function continent(): ?array
    {
        return $this->getArray('continent');
    }

    protected function country(?array $continent): ?array
    {
        $country = $this->getArray('country');

        return is_array($country) && is_array($continent)
            ? [
                ...$country,
                'continent' => $continent,
            ]
            : null;
    }

    protected function city(?array $country): ?array
    {
        $city = $this->getArray('city');
        $subdivisions = $this->getArray('subdivisions');

        return is_array($city) && is_array($country)
            ? [
                ...$city,
                'country' => $country,
                'subdivisions' => $subdivisions,
            ]
            : null;
    }

    protected function registeredCountry(?array $continent): ?array
    {
        $registeredCountry = $this->getArray('registered_country');

        return is_array($registeredCountry) && is_array($continent)
            ? [
                ...$registeredCountry,
                'continent' => $continent,
            ]
            : null;
    }

    protected function accuracyRadius(): ?int
    {
        return $this->getInt('location.accuracy_radius');
    }

    protected function latitude(): ?float
    {
        return $this->getFloat('location.latitude');
    }

    protected function longitude(): ?float
    {
        return $this->getFloat('location.longitude');
    }

    protected function timeZone(): ?string
    {
        return $this->getString('location.time_zone');
    }

    protected function postalCode(): ?string
    {
        return $this->getString('postal.code');
    }
}
