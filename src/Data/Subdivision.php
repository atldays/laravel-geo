<?php

namespace Atldays\Geo\Data;

use Atldays\Geo\Contracts\SubdivisionContract;
use Atldays\Geo\Data\Casts\LocalizedNameCast;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class Subdivision extends Data implements SubdivisionContract
{
    public function __construct(
        #[MapName('geoname_id')]
        public readonly int $geoNameId,
        #[MapInputName('names')]
        #[WithCast(LocalizedNameCast::class)]
        public readonly string $name,
        #[MapName('iso_code')]
        public readonly string $isoCode,
    ) {}

    public function getGeoNameId(): int
    {
        return $this->geoNameId;
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
