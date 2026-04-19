<?php

namespace Atldays\Geo\Data;

use Atldays\Geo\Contracts\UpdateResultContract;
use Spatie\LaravelData\Data;

class UpdateResult extends Data implements UpdateResultContract
{
    public function __construct(
        public readonly bool $downloaded,
        public readonly string $path,
        public readonly string $metadataPath,
    ) {}

    public function isDownloaded(): bool
    {
        return $this->downloaded;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getMetadataPath(): string
    {
        return $this->metadataPath;
    }
}
