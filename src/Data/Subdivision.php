<?php

namespace Atldays\Geo\Data;

use Atldays\Geo\Contracts\SubdivisionContract;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class Subdivision extends Data implements SubdivisionContract
{
    public function __construct(
        public readonly string $name,
        #[MapName('iso_code')]
        public readonly string $isoCode,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getIsoCode(): string
    {
        return $this->isoCode;
    }
}
