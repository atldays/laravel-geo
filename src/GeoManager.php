<?php

namespace Atldays\Geo;

use Atldays\Geo\Contracts\GeoDriver;
use Atldays\Geo\Contracts\GeoResultContract;
use Atldays\Geo\Data\GeoResult;
use Atldays\Geo\Exceptions\DriverUnavailableException;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use RuntimeException;

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
    public function ip(string $ip): GeoResultContract
    {
        $exception = null;

        foreach ($this->drivers() as $driver) {
            try {
                return $driver->locate($ip);
            } catch (DriverUnavailableException $exception) {
                continue;
            }
        }

        if ($exception instanceof DriverUnavailableException) {
            throw new DriverUnavailableException(
                'All configured geo drivers failed to resolve the provided IP.',
                previous: $exception,
            );
        }

        return GeoResult::unresolved($ip);
    }

    /**
     * @throws BindingResolutionException
     */
    public function request(?Request $request = null): GeoResultContract
    {
        $request ??= $this->container->make('request');

        if (!$request instanceof Request) {
            throw new RuntimeException('Unable to resolve the current Laravel request instance.');
        }

        return $this->ip($request->fakeClientIp() ?? $request->realClientIp());
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
            if (
                !is_string($driverClass)
                || !is_a($driverClass, GeoDriver::class, true)
            ) {
                throw new RuntimeException(sprintf(
                    'Configured geo driver [%s] must implement [%s].',
                    is_string($driverClass) ? $driverClass : get_debug_type($driverClass),
                    GeoDriver::class,
                ));
            }

            $this->loaded[] = $this->container->make($driverClass);
        }

        return $this->loaded;
    }
}
