<?php

namespace Atldays\Geo\Contracts;

interface GeoMatcher
{
    public function matchGeo(GeoContract $geo): bool;
}
