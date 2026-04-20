<?php

namespace Atldays\Geo\Exceptions;

class DefinitionUnavailable extends CountryDefinitionException
{
    public static function forPackage(string $provider, string $package): self
    {
        return new self(sprintf(
            'The country definition provider [%s] requires package [%s], but it is not installed.',
            $provider,
            $package,
        ));
    }
}
