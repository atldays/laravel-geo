<?php

namespace Atldays\Geo\Drivers;

use Atldays\Geo\Contracts\{GeoDataContract, GeoDriverUpdatable, UpdateResultContract};
use Atldays\Geo\Data\{GeoData, MaxMindConfig, UpdateOptions};
use Atldays\Geo\Exceptions\DriverUnavailableException;
use Atldays\Geo\Updaters\MaxMindUpdater;
use Illuminate\Http\Client\ConnectionException;
use MaxMind\Db\Reader;
use Random\RandomException;
use Throwable;

class MaxMind extends AbstractDriver implements GeoDriverUpdatable
{
    protected ?Reader $reader = null;

    public function __construct(
        protected MaxMindConfig $config,
        protected MaxMindUpdater $updater,
    ) {}

    /**
     * @throws ConnectionException|RandomException
     */
    public function update(UpdateOptions $options): UpdateResultContract
    {
        return $this->updater->update($options);
    }

    protected function fetch(): array
    {
        try {
            $record = $this->reader()->get($this->ip());
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

    protected function provider(): string
    {
        return 'MaxMind';
    }

    protected function result(): GeoDataContract
    {
        $continent = $this->continent();
        $country = $this->country($continent);

        return GeoData::from([
            'ip' => $this->ip(),
            'provider' => $this->provider(),
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
        $continent = $this->dataArray('continent');

        return is_array($continent)
            ? [
                'name' => $this->localizedName($continent['names'] ?? null),
                'code' => $continent['code'] ?? null,
                'external_id' => $continent['geoname_id'] ?? null,
            ]
            : null;
    }

    protected function country(?array $continent): ?array
    {
        $country = $this->dataArray('country');

        return is_array($country) && is_array($continent)
            ? [
                'name' => $this->localizedName($country['names'] ?? null),
                'iso_code' => $country['iso_code'] ?? null,
                'continent' => $continent,
                'external_id' => $country['geoname_id'] ?? null,
            ]
            : null;
    }

    protected function city(?array $country): ?array
    {
        $city = $this->dataArray('city');
        $subdivisions = $this->dataArray('subdivisions');

        return is_array($city) && is_array($country)
            ? [
                'name' => $this->localizedName($city['names'] ?? null),
                'country' => $country,
                'subdivisions' => $this->normalizeSubdivisions($subdivisions),
                'external_id' => $city['geoname_id'] ?? null,
            ]
            : null;
    }

    protected function registeredCountry(?array $continent): ?array
    {
        $registeredCountry = $this->dataArray('registered_country');

        return is_array($registeredCountry) && is_array($continent)
            ? [
                'name' => $this->localizedName($registeredCountry['names'] ?? null),
                'iso_code' => $registeredCountry['iso_code'] ?? null,
                'continent' => $continent,
                'external_id' => $registeredCountry['geoname_id'] ?? null,
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

    protected function normalizeSubdivisions(?array $subdivisions): ?array
    {
        if ($subdivisions === null) {
            return null;
        }

        $normalized = array_values(array_filter(array_map(function (mixed $subdivision): ?array {
            if (!is_array($subdivision)) {
                return null;
            }

            return [
                'name' => $this->localizedName($subdivision['names'] ?? null),
                'iso_code' => $subdivision['iso_code'] ?? null,
                'external_id' => $subdivision['geoname_id'] ?? null,
            ];
        }, $subdivisions)));

        return $normalized === [] ? null : $normalized;
    }

    protected function localizedName(mixed $value): ?string
    {
        if (is_string($value)) {
            $value = trim($value);

            return $value === '' ? null : $value;
        }

        if (!is_array($value)) {
            return null;
        }

        $preferred = $value['en'] ?? null;

        if (is_string($preferred) && trim($preferred) !== '') {
            return trim($preferred);
        }

        foreach ($value as $name) {
            if (is_string($name) && trim($name) !== '') {
                return trim($name);
            }
        }

        return null;
    }
}
