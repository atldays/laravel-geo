<?php

namespace Atldays\Geo\Contracts;

interface UpdateResultContract
{
    public function isDownloaded(): bool;

    public function getPath(): string;

    public function getMetadataPath(): string;
}
