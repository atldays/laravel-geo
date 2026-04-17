<?php

namespace Atldays\Geo\Updaters;

use Atldays\Geo\Contracts\Updatable;
use Atldays\Geo\Data\MaxMindConfig;
use Atldays\Geo\Data\UpdateOptions;
use Atldays\Geo\Data\UpdateResult;
use FilesystemIterator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;
use JsonException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;

class MaxMindUpdater implements Updatable
{
    public function __construct(
        protected MaxMindConfig $config,
        protected Filesystem $files,
    ) {}

    /**
     * @throws ConnectionException
     */
    public function update(UpdateOptions $options): UpdateResult
    {
        $editionId = $this->config->editionId;
        $downloadUrl = $this->config->downloadUrl ?? $this->buildDownloadUrl($editionId);
        $force = $options->force;

        $this->config->requireCredentials();
        $this->config->requireDownloadSource($editionId, $downloadUrl);

        $headers = $this->fetchHeaders($downloadUrl);
        $lastModified = $headers['last-modified'] ?? null;
        $targetPath = $this->resolveExistingDatabasePath();

        if (!$force && $targetPath && !$this->shouldDownload($targetPath, $lastModified)) {
            return new UpdateResult(
                downloaded: false,
                editionId: $editionId,
                databasePath: $targetPath,
                metadataPath: $this->config->metadataPath(),
                remoteLastModified: $lastModified,
            );
        }

        $workingDirectory = $this->temporaryDirectory();
        $archivePath = $workingDirectory . DIRECTORY_SEPARATOR . 'database.tar.gz';

        $this->files->ensureDirectoryExists($workingDirectory);
        $this->files->ensureDirectoryExists($this->config->databasePath);

        try {
            $this->downloadArchive($downloadUrl, $archivePath);

            $databasePath = $this->extractDatabase($archivePath, $workingDirectory);
            $finalPath = $this->storeDatabaseFile($databasePath);

            $this->writeMetadata([
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
                metadataPath: $this->config->metadataPath(),
                remoteLastModified: $lastModified,
            );
        } finally {
            $this->files->deleteDirectory($workingDirectory);
        }
    }

    /**
     * @throws ConnectionException
     */
    protected function fetchHeaders(string $downloadUrl): array
    {
        $response = $this->http()->withOptions([
            'allow_redirects' => true,
        ])->head($downloadUrl);

        if (!$response->successful()) {
            throw new RuntimeException(sprintf(
                'Failed to read MaxMind release headers (%s).',
                $response->status(),
            ));
        }

        return $this->normalizeHeaders($response->headers());
    }

    /**
     * @throws ConnectionException
     */
    protected function downloadArchive(string $downloadUrl, string $archivePath): void
    {
        $response = $this->http()->withOptions([
            'allow_redirects' => true,
            'sink' => $archivePath,
        ])->get($downloadUrl);

        if (!$response->successful()) {
            throw new RuntimeException(sprintf(
                'Failed to download MaxMind archive (%s).',
                $response->status(),
            ));
        }
    }

    protected function http(): PendingRequest
    {
        return Http::withBasicAuth($this->config->accountId, $this->config->licenseKey);
    }

    protected function extractDatabase(string $archivePath, string $workingDirectory): string
    {
        if (!class_exists(\PharData::class)) {
            throw new RuntimeException('The PHP phar extension is required to extract MaxMind tar.gz archives.');
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

        throw new RuntimeException('MaxMind archive was downloaded, but no .mmdb file was found inside it.');
    }

    protected function storeDatabaseFile(string $databasePath): string
    {
        $destination = $this->config->resolveDatabasePath(basename($databasePath));
        $temporaryDestination = $destination . '.tmp';

        $this->files->copy($databasePath, $temporaryDestination);
        $this->files->move($temporaryDestination, $destination);

        return $destination;
    }

    protected function resolveExistingDatabasePath(): ?string
    {
        if ($this->config->databaseFilename) {
            return $this->config->resolveDatabasePath();
        }

        if (!$this->files->isDirectory($this->config->databasePath)) {
            return null;
        }

        $matches = $this->files->glob($this->config->databasePath . DIRECTORY_SEPARATOR . '*.mmdb');

        return $matches[0] ?? null;
    }

    protected function shouldDownload(string $localPath, ?string $remoteLastModified): bool
    {
        if (!$this->files->exists($localPath)) {
            return true;
        }

        if ($remoteLastModified === null) {
            return true;
        }

        $remoteTimestamp = strtotime($remoteLastModified);
        $localTimestamp = @filemtime($localPath);

        if ($remoteTimestamp === false || $localTimestamp === false) {
            return true;
        }

        return $remoteTimestamp > $localTimestamp;
    }

    protected function buildDownloadUrl(?string $editionId): string
    {
        if (!$editionId) {
            throw new RuntimeException('A MaxMind edition ID is required to build a download URL.');
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

    protected function normalizeHeaders(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $name => $values) {
            $normalized[strtolower($name)] = is_array($values) ? ($values[0] ?? null) : $values;
        }

        return $normalized;
    }

    /**
     * @throws JsonException
     */
    protected function writeMetadata(array $payload): void
    {
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        $this->files->put($this->config->metadataPath(), $json . PHP_EOL);
    }

    protected function temporaryDirectory(): string
    {
        return $this->config->databasePath . DIRECTORY_SEPARATOR . '.tmp-maxmind-' . bin2hex(random_bytes(5));
    }
}
