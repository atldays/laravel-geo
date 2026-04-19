<?php

namespace Atldays\Geo\Contracts;

interface ContinentContract extends GeoLocationContract
{
    public function getCode(): string;
}
