<?php

namespace Tests\Feature;

use Atldays\Geo\Data\{MaxMindConfig, UpdateOptions};
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
}
