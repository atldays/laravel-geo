<?php

namespace Atldays\Geo\Exceptions;

use RuntimeException;

class GeoException extends RuntimeException
{
    public static function unableToResolveCurrentRequest(): self
    {
        return new self('Unable to resolve the current Laravel request instance.');
    }

    public static function invalidConfiguredDriver(string $driverClass, string $contract): self
    {
        return new self(sprintf(
            'Configured geo driver [%s] must implement [%s].',
            $driverClass,
            $contract,
        ));
    }
}
