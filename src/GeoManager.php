<?php

namespace Atldays\Geo;

use Atldays\Geo\Contracts\{GeoDataContract, GeoDriver};
use Atldays\Geo\Data\GeoData;
use Atldays\Geo\Exceptions\{DriverUnavailableException, GeoException};
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Container\{BindingResolutionException, Container};
use Illuminate\Http\Request;

class GeoManager
{
    protected array $loaded = [];

    public function __construct(
        protected Config $config,
        protected Container $container,
    ) {}

    /**
     * @throws BindingResolutionException
     */
    public function ip(string $ip): GeoDataContract
    {
        $exception = null;

        foreach ($this->drivers() as $driver) {
            try {
                return $driver->resolve($ip);
            } catch (DriverUnavailableException $exception) {
                continue;
            }
        }

        if ($exception instanceof DriverUnavailableException) {
            throw DriverUnavailableException::allDriversFailed($exception);
        }

        return GeoData::unresolved($ip);
    }

    /**
     * @throws BindingResolutionException
     */
    public function request(?Request $request = null): GeoDataContract
    {
        $request ??= $this->container->make('request');

        if (!$request instanceof Request) {
            throw GeoException::unableToResolveCurrentRequest();
        }

        return $this->ip($request->fakeIp() ?? $request->realIp());
    }

    /**
     * @return GeoDriver[]
     *
     * @throws BindingResolutionException
     */
    public function drivers(): array
    {
        if ($this->loaded !== []) {
            return $this->loaded;
        }

        $drivers = array_values(array_filter([
            $this->config->get('geo.driver'),
            ...$this->config->get('geo.fallbacks', []),
        ]));

        foreach ($drivers as $driverClass) {
            if (!is_string($driverClass) || !is_a($driverClass, GeoDriver::class, true)) {
                throw GeoException::invalidConfiguredDriver(
                    is_string($driverClass) ? $driverClass : get_debug_type($driverClass),
                    GeoDriver::class,
                );
            }

            $this->loaded[] = $this->container->make($driverClass);
        }

        return $this->loaded;
    }
}
