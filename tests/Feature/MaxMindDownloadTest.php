<?php

namespace Tests\Feature;

use Atldays\Geo\Data\UpdateOptions;
use Atldays\Geo\Exceptions\DriverUnavailableException;
use Atldays\Geo\Updaters\MaxMindUpdater;
use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

class MaxMindDownloadTest extends TestCase
{
    public function test_maxmind_database_can_be_downloaded_from_live_service(): void
    {
        if (!env('MAXMIND_ACCOUNT_ID') || !env('MAXMIND_LICENSE_KEY')) {
            $this->markTestSkipped('MaxMind credentials are not configured in .env.');
        }

        $files = $this->app->make(Filesystem::class);
        $databasePath = (string)config('geo.maxmind.database_path');

        $files->deleteDirectory($databasePath);
        $files->ensureDirectoryExists($databasePath);

        try {
            $result = $this->app->make(MaxMindUpdater::class)->update(new UpdateOptions(force: true));
        } catch (DriverUnavailableException $exception) {
            if (str_contains($exception->getMessage(), '(429)')) {
                $this->markTestSkipped('MaxMind rate-limited the live download test.');
            }

            throw $exception;
        }

        $this->assertTrue($files->exists($result->databasePath));
        $this->assertSame('mmdb', pathinfo($result->databasePath, PATHINFO_EXTENSION));
        $this->assertGreaterThan(0, (int)$files->size($result->databasePath));
        $this->assertTrue($files->exists($result->metadataPath));
        $this->assertNotNull(json_decode($files->get($result->metadataPath), true));
    }
}
