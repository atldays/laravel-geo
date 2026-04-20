<?php

namespace Atldays\Geo\Contracts;

interface DriverContract
{
    public function resolve(string $ip): GeoContract;
}
