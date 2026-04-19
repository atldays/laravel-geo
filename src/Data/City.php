<?php

namespace Atldays\Geo\Data;

use Atldays\Geo\Contracts\{CityContract, CountryContract};
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

class City extends Data implements CityContract
{
    public function __construct(
        public readonly string $name,
        public readonly Country $country,
        #[DataCollectionOf(Subdivision::class)]
        public readonly ?Collection $subdivisions = null,
    ) {}

    public function getCountry(): CountryContract
    {
        return $this->country;
    }

    /**
     * @return Collection<Subdivision>
     */
    public function getSubdivisions(): Collection
    {
        return $this->subdivisions ?? collect();
    }

    public function getName(): string
    {
        return $this->name;
    }
}
