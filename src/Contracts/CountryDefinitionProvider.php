<?php

namespace Atldays\Geo\Contracts;

interface CountryDefinitionProvider
{
    public function resolve(string $isoAlpha2): CountryDefinitionContract;
}
