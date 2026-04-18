<?php

namespace Atldays\Geo\Data;

use Atldays\Geo\Contracts\ContinentContract;
use Atldays\Geo\Data\Casts\LocalizedNameCast;
use Spatie\LaravelData\Attributes\{MapInputName, MapName, WithCast};
use Spatie\LaravelData\Data;

class Continent extends Data implements ContinentContract
{
    public function __construct(
        #[MapName('geoname_id')]
        public readonly int $geoNameId,
        #[MapInputName('names')]
        #[WithCast(LocalizedNameCast::class)]
        public readonly string $name,
        public readonly string $code,
    ) {}

    public function getCode(): string
    {
        return $this->code;
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
