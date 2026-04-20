<?php

namespace Atldays\Geo\Drivers;

use Atldays\Geo\Contracts\{GeoDataContract, GeoDriver};
use Atldays\Geo\Data\GeoData;
use Atldays\Geo\Exceptions\DriverException;

abstract class AbstractDriver implements GeoDriver
{
    use Concerns\InteractsWithData;

    private ?string $ip = null;

    private array $data = [];

    /**
     * Resolve the provided IP into normalized geo data.
     *
     * This method controls the full driver pipeline: validate the IP, fetch
     * raw source data, cache it on the current driver instance, and then build
     * the final result object from that cached data.
     */
    final public function resolve(string $ip): GeoDataContract
    {
        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            throw DriverException::invalidIp($ip);
        }

        $this->ip = $ip;
        $this->data = [];

        $this->data = $this->fetch();

        if ($this->data === []) {
            return GeoData::unresolved($ip, $this->provider());
        }

        return $this->result();
    }

    /**
     * Fetch raw data for the current IP from the underlying source.
     *
     * Implementations should only retrieve a source-specific payload and return
     * it as an array. They should not build the final GeoData DTO here.
     */
    abstract protected function fetch(): array;

    /**
     * Build the final normalized result from the cached raw driver data.
     *
     * At this point the current IP and fetched raw payload are already stored
     * on the driver instance and should be accessed through the helper methods
     * exposed by this abstract class.
     */
    abstract protected function result(): GeoDataContract;

    /**
     * Get the IP currently being resolved by the driver.
     */
    protected function ip(): string
    {
        if ($this->ip === null) {
            throw DriverException::missingIp();
        }

        return $this->ip;
    }

    /**
     * Get the raw payload currently cached on the driver instance.
     */
    protected function data(): array
    {
        return $this->data;
    }

    protected function provider(): string
    {
        return class_basename(static::class);
    }
}
