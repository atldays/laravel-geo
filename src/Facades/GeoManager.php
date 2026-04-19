<?php

namespace Atldays\Geo\Facades;

use Atldays\Geo\Contracts\GeoDataContract;
use Atldays\Geo\GeoManager as GeoManagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;

/**
 * @method static GeoDataContract ip(string $ip)
 * @method static GeoDataContract request(?Request $request = null)
 * @method static array drivers()
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
