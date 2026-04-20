<?php

namespace Atldays\Geo\Data;

use Atldays\Geo\Contracts\{ContinentContract, CountryContract, CountryDefinitionContract};
use Atldays\Geo\CountryDefinitionManager;
use Illuminate\Support\Facades\App;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class Country extends Data implements CountryContract
{
    public function __construct(
        public readonly string $name,
        #[MapName('iso_code')]
        public readonly string $isoCode,
        public readonly Continent $continent,
        #[MapName('external_id')]
        public readonly int|string|null $externalId = null,
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

    public function getExternalId(): int|string|null
    {
        return $this->externalId;
    }

    public function definition(): CountryDefinitionContract
    {
        return App::make(CountryDefinitionManager::class)->isoCode($this->isoCode);
    }
}
