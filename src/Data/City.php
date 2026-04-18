<?php

namespace Atldays\Geo\Data;

use Atldays\Geo\Contracts\{CityContract, CountryContract};
use Atldays\Geo\Data\Casts\LocalizedNameCast;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\{DataCollectionOf, MapInputName, MapName, WithCast};
use Spatie\LaravelData\Data;

class City extends Data implements CityContract
{
    public function __construct(
        #[MapName('geoname_id')]
        public readonly int $geoNameId,
        #[MapInputName('names')]
        #[WithCast(LocalizedNameCast::class)]
        public readonly string $name,
        public readonly Country $country,
        #[DataCollectionOf(Subdivision::class)]
        public readonly ?Collection $subdivisions = null,
    ) {}

    public function getCountry(): CountryContract
    {
        return $this->country;
    }

    public function getSubdivisions(): Collection
    {
        return $this->subdivisions ?? collect();
    }

    public function getGeoNameId(): int
    {
        return $this->geoNameId;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
