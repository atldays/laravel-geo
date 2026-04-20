<?php

namespace Atldays\Geo\Exceptions;

use Throwable;

class DefinitionNotFound extends CountryDefinitionException
{
    public static function forIsoAlpha2(string $isoAlpha2, ?Throwable $previous = null): self
    {
        return new self(
            sprintf('The country definition could not be resolved for ISO alpha-2 code [%s].', $isoAlpha2),
            previous: $previous,
        );
    }
}
