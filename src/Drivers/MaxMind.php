<?php

namespace Atldays\Geo\Drivers;

use Atldays\Geo\Contracts\GeoDataContract;
use Atldays\Geo\Contracts\GeoDriverUpdatable;
use Atldays\Geo\Data\GeoData;
use Atldays\Geo\Data\MaxMindConfig;
use Atldays\Geo\Data\UpdateOptions;
use Atldays\Geo\Data\UpdateResult;
use Atldays\Geo\Exceptions\DriverUnavailableException;
use Atldays\Geo\Updaters\MaxMindUpdater;
use Illuminate\Http\Client\ConnectionException;
use MaxMind\Db\Reader;
use Throwable;

class MaxMind extends AbstractDriver implements GeoDriverUpdatable
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

    protected function fetch(): array
    {
        try {
            $record = $this->reader()->get((string)$this->ip());
        } catch (DriverUnavailableException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw DriverUnavailableException::because(
                self::class,
                'The database reader could not process the requested IP.',
                $exception,
            );
        }

        return is_array($record) ? $record : [];
    }

    protected function result(): GeoDataContract
    {
        $continent = $this->continent();
        $country = $this->country($continent);

        return GeoData::from([
            'ip' => $this->ip(),
            'continent' => $continent,
            'country' => $country,
            'city' => $this->city($country),
            'registered_country' => $this->registeredCountry($continent),
            'accuracy_radius' => $this->accuracyRadius(),
            'latitude' => $this->latitude(),
            'longitude' => $this->longitude(),
            'time_zone' => $this->timeZone(),
            'postal_code' => $this->postalCode(),
            'data' => $this->data(),
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
        return $this->dataArray('continent');
    }

    protected function country(?array $continent): ?array
    {
        $country = $this->dataArray('country');

        return is_array($country) && is_array($continent)
            ? [
                ...$country,
                'continent' => $continent,
            ]
            : null;
    }

    protected function city(?array $country): ?array
    {
        $city = $this->dataArray('city');
        $subdivisions = $this->dataArray('subdivisions');

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
        $registeredCountry = $this->dataArray('registered_country');

        return is_array($registeredCountry) && is_array($continent)
            ? [
                ...$registeredCountry,
                'continent' => $continent,
            ]
            : null;
    }

    protected function accuracyRadius(): ?int
    {
        return $this->dataInt('location.accuracy_radius');
    }

    protected function latitude(): ?float
    {
        return $this->dataFloat('location.latitude');
    }

    protected function longitude(): ?float
    {
        return $this->dataFloat('location.longitude');
    }

    protected function timeZone(): ?string
    {
        return $this->dataString('location.time_zone');
    }

    protected function postalCode(): ?string
    {
        return $this->dataString('postal.code');
    }
}
