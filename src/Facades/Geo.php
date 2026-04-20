<?php

namespace Atldays\Geo\Facades;

use Atldays\Geo\Contracts\{CityContract, ContinentContract, CountryContract, GeoContract};
use Illuminate\Support\Facades\Facade;

/**
 * @mixin GeoContract
 *
 * @method static string ip()
 * @method static string provider()
 * @method static ?ContinentContract continent()
 * @method static ?CountryContract country()
 * @method static ?CityContract city()
 * @method static ?CountryContract registeredCountry()
 * @method static ?int accuracyRadius()
 * @method static ?float latitude()
 * @method static ?float longitude()
 * @method static ?string timeZone()
 * @method static ?string postalCode()
 * @method static array data()
 * @method static array toArray()
 *
 * @see GeoContract
 */
class Geo extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'geo';
    }
}
