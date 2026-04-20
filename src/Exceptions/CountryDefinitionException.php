<?php

namespace Atldays\Geo\Exceptions;

use RuntimeException;
use Throwable;

class CountryDefinitionException extends RuntimeException
{
    public static function because(string $message, ?Throwable $previous = null): self
    {
        return new self($message, previous: $previous);
    }
}
