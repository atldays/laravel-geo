<?php

namespace Atldays\Geo\Concerns;

use Illuminate\Support\Arr;

trait InteractsWithData
{
    abstract protected function data(): array;

    protected function dataValue(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->data(), $key, $default);
    }

    protected function dataArray(string $key): ?array
    {
        $value = $this->dataValue($key);

        return $this->valueArray($value);
    }

    protected function dataInt(string $key): ?int
    {
        $value = $this->dataValue($key);

        return is_numeric($value) ? (int)$value : null;
    }

    protected function dataFloat(string $key): ?float
    {
        $value = $this->dataValue($key);

        return is_numeric($value) ? (float)$value : null;
    }

    protected function dataString(string $key): ?string
    {
        $value = $this->dataValue($key);

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    protected function dataBool(string $key): ?bool
    {
        $value = $this->dataValue($key);

        return is_bool($value) ? $value : null;
    }

    protected function valueArray(mixed $value): ?array
    {
        return is_array($value) && $value !== [] ? $value : null;
    }
}
