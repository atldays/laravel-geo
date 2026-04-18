<?php

namespace Atldays\Geo\Contracts;

use Illuminate\Contracts\Support\Arrayable;

interface GeoLocationContract extends Arrayable
{
    public function getGeoNameId(): int;

    public function getName(): string;
}
