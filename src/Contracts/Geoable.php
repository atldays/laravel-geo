<?php

namespace Atldays\Geo\Contracts;

interface Geoable
{
    public function getGeo(): GeoContract;
}
