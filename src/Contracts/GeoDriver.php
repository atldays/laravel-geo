<?php

namespace Atldays\Geo\Contracts;

interface GeoDriver
{
    public function resolve(string $ip): GeoDataContract;
}
