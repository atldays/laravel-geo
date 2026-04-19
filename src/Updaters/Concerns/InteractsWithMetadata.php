<?php

namespace Atldays\Geo\Updaters\Concerns;

use Illuminate\Support\Facades\File;
use JsonException;

trait InteractsWithMetadata
{
    /**
     * Persist updater metadata as pretty-printed JSON.
     *
     * @throws JsonException
     */
    protected function writeMetadata(string $path, array $payload): void
    {
        $metadata = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        File::put($path, $metadata . PHP_EOL);
    }
}
