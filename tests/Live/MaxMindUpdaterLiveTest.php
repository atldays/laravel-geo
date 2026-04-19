<?php

namespace Tests\Live;

use Atldays\Geo\Data\UpdateOptions;
use Atldays\Geo\Exceptions\DriverUnavailableException;
use Atldays\Geo\Updaters\MaxMindUpdater;
use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

class MaxMindUpdaterLiveTest extends TestCase
{
    public function test_maxmind_database_can_be_downloaded_from_live_service(): void
    {
        if (!env('MAXMIND_ACCOUNT_ID') || !env('MAXMIND_LICENSE_KEY')) {
            $this->markTestSkipped('MaxMind credentials are not configured.');
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

        $this->assertTrue($result->isDownloaded());
        $this->assertTrue($files->exists($result->getPath()));
        $this->assertSame('mmdb', pathinfo($result->getPath(), PATHINFO_EXTENSION));
        $this->assertGreaterThan(0, (int)$files->size($result->getPath()));
        $this->assertTrue($files->exists($result->getMetadataPath()));

        $metadata = json_decode($files->get($result->getMetadataPath()), true);

        $this->assertIsArray($metadata);
        $this->assertSame((string)config('geo.maxmind.edition_id'), $metadata['edition_id'] ?? null);
        $this->assertSame($result->getPath(), $metadata['database_path'] ?? null);
        $this->assertNotEmpty($metadata['downloaded_at'] ?? null);
    }
}
