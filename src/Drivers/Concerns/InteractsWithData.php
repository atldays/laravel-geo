<?php

namespace Atldays\Geo\Drivers\Concerns;

use Illuminate\Support\Arr;

trait InteractsWithData
{
    /**
     * Read a raw value from the cached driver payload using dot notation.
     */
    protected function dataValue(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->data(), $key, $default);
    }

    /**
     * Read an array value from the cached driver payload.
     */
    protected function dataArray(string $key): ?array
    {
        $value = $this->dataValue($key);

        return is_array($value) && $value !== [] ? $value : null;
    }

    /**
     * Read an integer value from the cached driver payload.
     */
    protected function dataInt(string $key): ?int
    {
        $value = $this->dataValue($key);

        return is_numeric($value) ? (int)$value : null;
    }

    /**
     * Read a float value from the cached driver payload.
     */
    protected function dataFloat(string $key): ?float
    {
        $value = $this->dataValue($key);

        return is_numeric($value) ? (float)$value : null;
    }

    /**
     * Read a non-empty string value from the cached driver payload.
     */
    protected function dataString(string $key): ?string
    {
        $value = $this->dataValue($key);

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
