<?php

namespace Atldays\Geo\Exceptions;

class DriverException extends GeoException
{
    public static function invalidIp(string $ip): self
    {
        return new self(sprintf('The provided IP [%s] is invalid.', $ip));
    }
}
