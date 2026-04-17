<?php

namespace Atldays\Geo;

use Atldays\Geo\Drivers\AbstractDriver;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Container\Container;
use RuntimeException;

class GeoManager
{
    protected array $loaded = [];

    public function __construct(
        protected Config $config,
        protected Container $container,
    ) {}

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
            $driver = $this->container->make($driverClass);

            if (!$driver instanceof AbstractDriver) {
                throw new RuntimeException(sprintf(
                    'Configured geo driver [%s] must extend [%s].',
                    $driverClass,
                    AbstractDriver::class,
                ));
            }

            $this->loaded[] = $driver;
        }

        return $this->loaded;
    }
}
