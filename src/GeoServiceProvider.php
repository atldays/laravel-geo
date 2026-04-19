<?php

namespace Atldays\Geo;

use Atldays\Geo\Commands\UpdateCommand;
use Atldays\Geo\Data\{IpApiConfig, MaxMindConfig};
use Atldays\Geo\Drivers\{IpApi, MaxMind};
use Atldays\Geo\Updaters\MaxMindUpdater;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config as ConfigFacade;
use Spatie\LaravelPackageTools\{Package, PackageServiceProvider};

class GeoServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-geo')
            ->hasConfigFile('geo')
            ->hasCommand(UpdateCommand::class);
    }

    public function registeringPackage(): void
    {
        $this->app->singleton(GeoManager::class, function (Application $app): GeoManager {
            return new GeoManager(
                config: $app->make(Config::class),
                container: $app,
            );
        });

        $this->app->bind('geo', function (Application $app) {
            return $app->make(GeoManager::class)->request();
        });

        $this->registerMaxMind();
        $this->registerIpApi();
    }

    protected function registerMaxMind(): void
    {
        $this->app->singleton(MaxMindConfig::class, function (Application $app): MaxMindConfig {
            return MaxMindConfig::from($app->make(Config::class)->get('geo.maxmind', []));
        });

        $this->app->singleton(MaxMindUpdater::class, function (Application $app): MaxMindUpdater {
            return new MaxMindUpdater(
                config: $app->make(MaxMindConfig::class),
                files: $app->make(Filesystem::class),
            );
        });

        $this->app->bind(MaxMind::class, function (Application $app): MaxMind {
            return new MaxMind(
                config: $app->make(MaxMindConfig::class),
                updater: $app->make(MaxMindUpdater::class),
            );
        });
    }

    protected function registerIpApi(): void
    {
        $this->app->singleton(IpApiConfig::class, function (Application $app): IpApiConfig {
            return IpApiConfig::from($app->make(Config::class)->get('geo.ip_api', []));
        });

        $this->app->bind(IpApi::class, function (Application $app): IpApi {
            return new IpApi(
                config: $app->make(IpApiConfig::class),
            );
        });
    }

    public function packageBooted(): void
    {
        $this->registerRequestIpMacros();
    }

    protected function registerRequestIpMacros(): void
    {
        Request::macro('realClientIp', function (): string {
            /** @var Request $this */
            foreach ([
                'HTTP_CLIENT_IP',
                'HTTP_X_FORWARDED_FOR',
                'HTTP_X_FORWARDED',
                'HTTP_X_CLUSTER_CLIENT_IP',
                'HTTP_FORWARDED_FOR',
                'HTTP_FORWARDED',
                'REMOTE_ADDR',
            ] as $key) {
                if (!$this->server->has($key)) {
                    continue;
                }

                foreach (explode(',', (string)$this->server->get($key)) as $ip) {
                    $ip = trim($ip);

                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }

            return $this->ip() ?: '0.0.0.0';
        });

        Request::macro('fakeClientIp', function (): ?string {
            /** @var Request $this */
            $key = ConfigFacade::get('geo.request.fake_ip_key', 'ip');
            $ip = is_string($key) ? $this->input($key) : null;

            if (!filter_var(ConfigFacade::get('app.debug', false), FILTER_VALIDATE_BOOL) || !is_string($ip)) {
                return null;
            }

            return filter_var($ip, FILTER_VALIDATE_IP) !== false ? $ip : null;
        });
    }
}
