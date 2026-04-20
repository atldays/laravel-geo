<?php

namespace Atldays\Geo\Contracts;

interface CountryContract extends SubdivisionContract
{
    public function getContinent(): ContinentContract;

    public function definition(): CountryDefinitionContract;
}
