<?php

namespace Tests\Feature;

use Atldays\Geo\Data\{MaxMindConfig, UpdateOptions};
use Atldays\Geo\Exceptions\DriverUnavailableException;
use Atldays\Geo\Updaters\MaxMindUpdater;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MaxMindDownloadTest extends TestCase
{
    public function test_maxmind_updater_skips_download_when_local_database_is_current(): void
    {
        $files = $this->app->make(Filesystem::class);
        $databasePath = dirname(__DIR__, 2) . '/storage/app/testing/geo/maxmind-skip';
        $databaseFile = $databasePath . '/GeoLite2-City.mmdb';
        $remoteLastModified = gmdate(DATE_RFC7231, strtotime('-2 days'));

        $files->deleteDirectory($databasePath);
        $files->ensureDirectoryExists($databasePath);
        $files->put($databaseFile, 'local-database');
        touch($databaseFile, strtotime('-1 day'));

        Http::fake([
            '*' => Http::response('', 200, [
                'Last-Modified' => $remoteLastModified,
            ]),
        ]);

        try {
            $updater = new MaxMindUpdater(
                config: MaxMindConfig::from([
                    'account_id' => 'account-id',
                    'license_key' => 'license-key',
                    'edition_id' => 'GeoLite2-City',
                    'download_url' => 'https://example.test/maxmind',
                    'database_path' => $databasePath,
                    'database_filename' => 'GeoLite2-City.mmdb',
                    'metadata_filename' => 'maxmind-download.json',
                ]),
                files: $files,
            );

            $result = $updater->update(new UpdateOptions);

            $this->assertFalse($result->isDownloaded());
            $this->assertSame($databaseFile, $result->getPath());
            $this->assertSame($databasePath . '/maxmind-download.json', $result->getMetadataPath());
            $this->assertFalse($files->exists($result->getMetadataPath()));
            Http::assertSentCount(1);
            Http::assertSent(fn ($request) => $request->method() === 'HEAD');
        } finally {
            $files->deleteDirectory($databasePath);
        }
    }

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

        $this->assertTrue($result->isDownloaded());
        $this->assertTrue($files->exists($result->getPath()));
        $this->assertSame('mmdb', pathinfo($result->getPath(), PATHINFO_EXTENSION));
        $this->assertGreaterThan(0, (int)$files->size($result->getPath()));
        $this->assertTrue($files->exists($result->getMetadataPath()));
        $this->assertNotNull(json_decode($files->get($result->getMetadataPath()), true));
    }
}
