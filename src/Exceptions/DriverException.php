<?php

namespace Atldays\Geo\Exceptions;

class DriverException extends GeoException
{
    public static function invalidIp(string $driver, string $ip): self
    {
        return new self(sprintf(
            'The provided IP [%s] is invalid for driver [%s].',
            $ip,
            $driver,
        ));
    }
}
