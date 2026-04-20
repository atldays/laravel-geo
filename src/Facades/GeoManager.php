<?php

namespace Atldays\Geo\Facades;

use Atldays\Geo\Contracts\{DriverContract, GeoContract};
use Atldays\Geo\GeoManager as GeoManagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

/**
 * @mixin GeoManagerService
 *
 * @method static GeoContract ip(string $ip)
 * @method static GeoContract request(?Request $request = null)
 * @method static DriverContract[] drivers()
 *
 * @see GeoManagerService
 */
class GeoManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return GeoManagerService::class;
    }
}
