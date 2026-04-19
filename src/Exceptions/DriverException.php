<?php

namespace Atldays\Geo\Exceptions;

class DriverException extends GeoException
{
    public static function invalidIp(string $ip): self
    {
        return new self(sprintf('The provided IP [%s] is invalid.', $ip));
    }

    public static function missingIp(): self
    {
        return new self('The driver IP has not been initialized yet.');
    }
}
