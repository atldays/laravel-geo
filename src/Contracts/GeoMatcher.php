<?php

namespace Atldays\Geo\Contracts;

interface GeoMatcher
{
    public function matchGeo(GeoDataContract $geo): bool;
}
