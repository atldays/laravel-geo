<?php

namespace Atldays\Geo\Contracts;

interface SubdivisionContract extends GeoLocationContract
{
    public function getIsoCode(): string;
}
