<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class UpdateCommandTest extends TestCase
{
    public function test_update_command_is_registered(): void
    {
        Config::set('geo.maxmind.account_id', null);
        Config::set('geo.maxmind.license_key', null);

        $this->artisan('geo:update')
            ->expectsOutputToContain('Credentials are missing.')
            ->assertExitCode(1);
    }
}
