<?php

namespace Atldays\Geo\Data;

use Atldays\Geo\Contracts\{ContinentContract, CountryContract};
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class Country extends Data implements CountryContract
{
    public function __construct(
        public readonly string $name,
        #[MapName('iso_code')]
        public readonly string $isoCode,
        public readonly Continent $continent,
    ) {}

    public function getContinent(): ContinentContract
    {
        return $this->continent;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIsoCode(): string
    {
        return $this->isoCode;
    }
}
