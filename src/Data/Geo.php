<?php

namespace Atldays\Geo\Data;

use Atldays\Geo\Contracts\{CityContract, ContinentContract, CountryContract, GeoContract};
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class Geo extends Data implements GeoContract
{
    public function __construct(
        public readonly string $ip,
        public readonly string $provider,
        public readonly ?Continent $continent = null,
        public readonly ?Country $country = null,
        public readonly ?City $city = null,
        public readonly ?Country $registeredCountry = null,
        public readonly ?int $accuracyRadius = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly ?string $timeZone = null,
        public readonly ?string $postalCode = null,
        public readonly array $data = [],
    ) {}

    public static function unresolved(string $ip, string $provider = 'unresolved'): self
    {
        return new self(ip: $ip, provider: $provider);
    }

    public function ip(): string
    {
        return $this->ip;
    }

    public function provider(): string
    {
        return $this->provider;
    }

    public function continent(): ?ContinentContract
    {
        return $this->continent;
    }

    public function country(): ?CountryContract
    {
        return $this->country;
    }

    public function city(): ?CityContract
    {
        return $this->city;
    }

    public function registeredCountry(): ?CountryContract
    {
        return $this->registeredCountry;
    }

    public function accuracyRadius(): ?int
    {
        return $this->accuracyRadius;
    }

    public function latitude(): ?float
    {
        return $this->latitude;
    }

    public function longitude(): ?float
    {
        return $this->longitude;
    }

    public function timeZone(): ?string
    {
        return $this->timeZone;
    }

    public function postalCode(): ?string
    {
        return $this->postalCode;
    }

    public function data(): array
    {
        return $this->data;
    }
}
