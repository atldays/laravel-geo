<?php

namespace Atldays\Geo\Data;

use Atldays\Geo\Contracts\ContinentContract;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class Continent extends Data implements ContinentContract
{
    public function __construct(
        public readonly string $name,
        public readonly string $code,
        #[MapName('external_id')]
        public readonly int|string|null $externalId = null,
    ) {}

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getExternalId(): int|string|null
    {
        return $this->externalId;
    }
}
