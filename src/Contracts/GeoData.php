<?php

namespace Atldays\Geo\Contracts;

use Illuminate\Contracts\Support\Arrayable;

interface GeoData extends Arrayable
{
    public function data(): array;
}
