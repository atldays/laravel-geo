<?php

namespace Tests\Feature;

use Atldays\Geo\Data\MaxMindConfig;
use Atldays\Geo\Drivers\MaxMind;
use Atldays\Geo\Updaters\MaxMindUpdater;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class UpdateCommandTest extends TestCase
{
    public function test_update_command_is_registered(): void
    {
        Config::set('geo.driver', MaxMind::class);
        Config::set('geo.maxmind.account_id', null);
        Config::set('geo.maxmind.license_key', null);
        $this->app->forgetInstance(MaxMindConfig::class);
        $this->app->forgetInstance(MaxMindUpdater::class);

        $this->artisan('geo:update')
            ->expectsOutputToContain('Credentials are missing.')
            ->assertExitCode(1);
    }
}
