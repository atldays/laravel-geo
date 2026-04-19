<?php

namespace Atldays\Geo\Data;

use Spatie\LaravelData\Data;

class UpdateOptions extends Data
{
    public function __construct(
        public readonly bool $force = false,
    ) {}
}
