<?php

namespace Atldays\Geo\Exceptions;

class InvalidDefinitionProvider extends CountryDefinitionException
{
    public static function invalidConfiguredProvider(string $provider, string $expected): self
    {
        return new self(sprintf(
            'The configured country definition provider [%s] is invalid. Expected an implementation of [%s].',
            $provider,
            $expected,
        ));
    }
}
