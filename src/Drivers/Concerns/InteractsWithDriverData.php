<?php

namespace Atldays\Geo\Drivers\Concerns;

use Illuminate\Support\Arr;

trait InteractsWithDriverData
{
    protected array $data = [];

    protected function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->data, $key, $default);
    }

    protected function getArray(string $key): ?array
    {
        $value = $this->get($key);

        return is_array($value) && $value !== [] ? $value : null;
    }

    protected function getInt(string $key): ?int
    {
        $value = $this->get($key);

        return is_numeric($value) ? (int)$value : null;
    }

    protected function getFloat(string $key): ?float
    {
        $value = $this->get($key);

        return is_numeric($value) ? (float)$value : null;
    }

    protected function getString(string $key): ?string
    {
        $value = $this->get($key);

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
