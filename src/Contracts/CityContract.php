<?php

namespace Atldays\Geo\Contracts;

use Illuminate\Support\Collection;

interface CityContract extends GeoLocationContract
{
    public function getCountry(): CountryContract;

    public function getSubdivisions(): Collection;
}
