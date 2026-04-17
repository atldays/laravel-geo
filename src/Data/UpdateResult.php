<?php

namespace Atldays\Geo\Data;

use Spatie\LaravelData\Data;

class UpdateResult extends Data
{
    public function __construct(
        public readonly bool $downloaded,
        public readonly ?string $editionId,
        public readonly string $databasePath,
        public readonly string $metadataPath,
        public readonly ?string $remoteLastModified,
    ) {}
}
