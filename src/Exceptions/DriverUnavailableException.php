<?php

namespace Atldays\Geo\Exceptions;

use Throwable;

class DriverUnavailableException extends DriverException
{
    public static function because(string $driver, string $reason, ?Throwable $previous = null): self
    {
        return new self(sprintf(
            'Driver [%s] is unavailable: %s',
            $driver,
            $reason,
        ), previous: $previous);
    }

    public static function allDriversFailed(?Throwable $previous = null): self
    {
        return new self(
            'All configured geo drivers failed to resolve the provided IP.',
            previous: $previous,
        );
    }
}
