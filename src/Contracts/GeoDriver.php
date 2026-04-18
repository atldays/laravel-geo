<?php

namespace Atldays\Geo\Contracts;

interface GeoDriver
{
    public function locate(string $ip): GeoResultContract;
}
