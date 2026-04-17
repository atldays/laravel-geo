<?php

namespace Atldays\Geo\Data;

use RuntimeException;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class MaxMindConfig extends Data
{
    public function __construct(
        #[WithCast(Casts\EmptyStringToNullCast::class)]
        public ?string $accountId,
        #[WithCast(Casts\EmptyStringToNullCast::class)]
        public ?string $licenseKey,
        #[WithCast(Casts\EmptyStringToNullCast::class)]
        public ?string $editionId,
        #[WithCast(Casts\EmptyStringToNullCast::class)]
        public ?string $downloadUrl,
        public string $databasePath,
        #[WithCast(Casts\EmptyStringToNullCast::class)]
        public ?string $databaseFilename,
        public string $metadataFilename,
    ) {}

    public function requireCredentials(): void
    {
        if ($this->accountId && $this->licenseKey) {
            return;
        }

        throw new RuntimeException('MaxMind credentials are missing. Set MAXMIND_ACCOUNT_ID and MAXMIND_LICENSE_KEY.');
    }

    public function requireDownloadSource(?string $editionId, ?string $downloadUrl): void
    {
        if ($editionId || $downloadUrl) {
            return;
        }

        throw new RuntimeException('No MaxMind edition ID or download URL has been configured.');
    }

    public function metadataPath(): string
    {
        return $this->databasePath . DIRECTORY_SEPARATOR . $this->metadataFilename;
    }

    public function resolveDatabasePath(?string $discoveredFilename = null): string
    {
        $filename = $this->databaseFilename ?: $discoveredFilename;

        if (!$filename) {
            throw new RuntimeException('Unable to determine the MaxMind database filename.');
        }

        return $this->databasePath . DIRECTORY_SEPARATOR . $filename;
    }
}
