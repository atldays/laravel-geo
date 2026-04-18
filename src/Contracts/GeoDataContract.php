<?php

namespace Atldays\Geo\Contracts;

use Illuminate\Contracts\Support\Arrayable;

interface GeoDataContract extends Arrayable
{
    public function ip(): string;

    public function continent(): ?ContinentContract;

    public function country(): ?CountryContract;

    public function city(): ?CityContract;

    public function registeredCountry(): ?CountryContract;

    public function accuracyRadius(): ?int;

    public function latitude(): ?float;

    public function longitude(): ?float;

    public function timeZone(): ?string;

    public function postalCode(): ?string;

    public function data(): array;
}
