<?php

namespace Atldays\Geo\Contracts;

use Illuminate\Contracts\Support\Arrayable;

interface GeoLocationContract extends Arrayable
{
    public function getName(): string;

    public function getExternalId(): int|string|null;
}
