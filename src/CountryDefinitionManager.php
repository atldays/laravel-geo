<?php

namespace Atldays\Geo;

use Atldays\Geo\Contracts\{CountryDefinitionContract, CountryDefinitionProvider};
use Atldays\Geo\Exceptions\InvalidDefinitionProvider;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Container\{BindingResolutionException, Container};

class CountryDefinitionManager
{
    public function __construct(
        protected Config $config,
        protected Container $container,
    ) {}

    /**
     * @throws BindingResolutionException
     */
    public function isoCode(string $isoAlpha2): CountryDefinitionContract
    {
        return $this->provider()->resolve($isoAlpha2);
    }

    /**
     * @throws BindingResolutionException
     */
    public function provider(): CountryDefinitionProvider
    {
        $providerClass = $this->config->get('geo.definitions.country');

        if (!is_string($providerClass) || !is_a($providerClass, CountryDefinitionProvider::class, true)) {
            throw InvalidDefinitionProvider::invalidConfiguredProvider(
                is_string($providerClass) ? $providerClass : get_debug_type($providerClass),
                CountryDefinitionProvider::class,
            );
        }

        $provider = $this->container->make($providerClass);

        if (!$provider instanceof CountryDefinitionProvider) {
            throw InvalidDefinitionProvider::invalidConfiguredProvider(
                get_debug_type($provider),
                CountryDefinitionProvider::class,
            );
        }

        return $provider;
    }
}
