<?php

namespace Atldays\Geo\Updaters;

use Atldays\Geo\Contracts\GeoDriverUpdatable;
use Atldays\Geo\Data\MaxMindConfig;
use Atldays\Geo\Data\UpdateOptions;
use Atldays\Geo\Data\UpdateResult;
use Atldays\Geo\Exceptions\DriverUnavailableException;
use FilesystemIterator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use Random\RandomException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class MaxMindUpdater implements GeoDriverUpdatable
{
    use Concerns\InteractsWithMetadata;

    public function __construct(
        protected MaxMindConfig $config,
        protected Filesystem $files,
    ) {}

    /**
     * @throws ConnectionException|RandomException
     */
    public function update(UpdateOptions $options): UpdateResult
    {
        if (!$this->config->accountId || !$this->config->licenseKey) {
            throw DriverUnavailableException::maxMind(
                'Credentials are missing. Set MAXMIND_ACCOUNT_ID and MAXMIND_LICENSE_KEY.',
            );
        }

        $editionId = $this->config->editionId;
        $downloadUrl = $this->config->downloadUrl;
        $force = $options->force;

        if (!$downloadUrl) {
            if (!$editionId) {
                throw DriverUnavailableException::maxMind(
                    'No edition ID or download URL has been configured.',
                );
            }

            $downloadUrl = $this->buildDownloadUrl($editionId);
        }

        $headersResponse = $this->http()->withOptions([
            'allow_redirects' => true,
        ])->head($downloadUrl);

        if (!$headersResponse->successful()) {
            throw DriverUnavailableException::maxMind(
                sprintf('Failed to read release headers (%s).', $headersResponse->status()),
            );
        }

        $headers = [];

        foreach ($headersResponse->headers() as $name => $values) {
            $headers[strtolower($name)] = is_array($values) ? ($values[0] ?? null) : $values;
        }

        $lastModified = $headers['last-modified'] ?? null;

        $targetPath = null;

        if ($this->config->databaseFilename) {
            $targetPath = $this->config->getResolvedDatabasePath();
        } elseif ($this->files->isDirectory($this->config->getDatabaseDirectory())) {
            $matches = $this->files->glob($this->config->getDatabaseDirectory() . DIRECTORY_SEPARATOR . '*.mmdb');
            $targetPath = $matches[0] ?? null;
        }

        $shouldDownload = true;

        if ($targetPath && $this->files->exists($targetPath) && $lastModified !== null) {
            $remoteTimestamp = strtotime($lastModified);
            $localTimestamp = @filemtime($targetPath);

            if ($remoteTimestamp !== false && $localTimestamp !== false) {
                $shouldDownload = $remoteTimestamp > $localTimestamp;
            }
        } elseif ($targetPath && $this->files->exists($targetPath) && $lastModified === null) {
            $shouldDownload = true;
        }

        if (!$force && $targetPath && !$shouldDownload) {
            return new UpdateResult(
                downloaded: false,
                editionId: $editionId,
                databasePath: $targetPath,
                metadataPath: $this->config->getMetadataPath(),
                remoteLastModified: $lastModified,
            );
        }

        $workingDirectory = $this->config->getDatabaseDirectory() . DIRECTORY_SEPARATOR . '.tmp-maxmind-' . bin2hex(random_bytes(5));
        $archivePath = $workingDirectory . DIRECTORY_SEPARATOR . 'database.tar.gz';

        $this->files->ensureDirectoryExists($workingDirectory);
        $this->files->ensureDirectoryExists($this->config->getDatabaseDirectory());

        try {
            $downloadResponse = $this->http()->withOptions([
                'allow_redirects' => true,
                'sink' => $archivePath,
            ])->get($downloadUrl);

            if (!$downloadResponse->successful()) {
                throw DriverUnavailableException::maxMind(
                    sprintf('Failed to download the database archive (%s).', $downloadResponse->status()),
                );
            }

            $databasePath = $this->extractDatabase($archivePath, $workingDirectory);
            $finalPath = $this->config->getResolvedDatabasePath();
            $temporaryDestination = $finalPath . '.tmp';

            $this->files->copy($databasePath, $temporaryDestination);
            $this->files->move($temporaryDestination, $finalPath);

            $this->writeMetadata($this->config->getMetadataPath(), [
                'edition_id' => $editionId,
                'download_url' => $downloadUrl,
                'database_path' => $finalPath,
                'downloaded_at' => Date::now()->toIso8601String(),
                'remote_last_modified' => $lastModified,
                'content_disposition' => $headers['content-disposition'] ?? null,
            ]);

            if ($lastModified !== null && ($timestamp = strtotime($lastModified)) !== false) {
                @touch($finalPath, $timestamp);
            }

            return new UpdateResult(
                downloaded: true,
                editionId: $editionId,
                databasePath: $finalPath,
                metadataPath: $this->config->getMetadataPath(),
                remoteLastModified: $lastModified,
            );
        } finally {
            $this->files->deleteDirectory($workingDirectory);
        }
    }

    protected function http(): PendingRequest
    {
        return Http::withBasicAuth($this->config->accountId, $this->config->licenseKey);
    }

    protected function extractDatabase(string $archivePath, string $workingDirectory): string
    {
        if (!class_exists(\PharData::class)) {
            throw DriverUnavailableException::maxMind(
                'The PHP phar extension is required to extract tar.gz archives.',
            );
        }

        $tarPath = substr($archivePath, 0, -3);
        $extractDirectory = $workingDirectory . DIRECTORY_SEPARATOR . 'extracted';

        $this->files->ensureDirectoryExists($extractDirectory);

        $archive = new \PharData($archivePath);
        $archive->decompress();

        $tarArchive = new \PharData($tarPath);
        $tarArchive->extractTo($extractDirectory, null, true);

        $databasePath = $this->findDatabaseFile($extractDirectory);

        if ($databasePath !== null) {
            return $databasePath;
        }

        throw DriverUnavailableException::maxMind(
            'The archive was downloaded, but no .mmdb file was found inside it.',
        );
    }

    protected function buildDownloadUrl(?string $editionId): string
    {
        if (!$editionId) {
            throw DriverUnavailableException::maxMind(
                'An edition ID is required to build a download URL.',
            );
        }

        return sprintf(
            'https://download.maxmind.com/geoip/databases/%s/download?suffix=tar.gz',
            rawurlencode($editionId),
        );
    }

    protected function findDatabaseFile(string $directory): ?string
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'mmdb') {
                return $file->getRealPath();
            }
        }

        return null;
    }
}
