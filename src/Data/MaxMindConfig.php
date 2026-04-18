<?php

namespace Atldays\Geo\Data;

use InvalidArgumentException;
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

    public function getDatabaseDirectory(): string
    {
        return $this->databasePath;
    }

    public function getMetadataPath(): string
    {
        return $this->databasePath . DIRECTORY_SEPARATOR . $this->metadataFilename;
    }

    public function getDatabaseFilename(): string
    {
        if (is_string($this->databaseFilename) && $this->databaseFilename !== '') {
            return $this->databaseFilename;
        }

        if (is_string($this->editionId) && $this->editionId !== '') {
            return $this->editionId . '.mmdb';
        }

        throw new InvalidArgumentException('Unable to determine the MaxMind database filename.');
    }

    public function getResolvedDatabasePath(): string
    {
        return $this->databasePath . DIRECTORY_SEPARATOR . $this->getDatabaseFilename();
    }
}
