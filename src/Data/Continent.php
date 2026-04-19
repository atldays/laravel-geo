<?php

namespace Atldays\Geo\Data;

use Atldays\Geo\Contracts\ContinentContract;
use Spatie\LaravelData\Data;

class Continent extends Data implements ContinentContract
{
    public function __construct(
        public readonly string $name,
        public readonly string $code,
    ) {}

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
