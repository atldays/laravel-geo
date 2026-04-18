<?php

namespace Atldays\Geo\Concerns;

use Illuminate\Support\Arr;

trait InteractsWithData
{
    protected array $data = [];

    public function data(): array
    {
        return $this->data;
    }

    protected function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->data, $key, $default);
    }

    protected function getToIntOrNull(string $key): ?int
    {
        $value = $this->get($key);

        return is_numeric($value) ? (int)$value : null;
    }

    protected function getToFloatOrNull(string $key): ?float
    {
        $value = $this->get($key);

        return is_numeric($value) ? (float)$value : null;
    }

    protected function getToArrayOrNull(string $key): ?array
    {
        $value = $this->get($key);

        return is_array($value) && $value !== [] ? $value : null;
    }

    protected function getToStringOrNull(string $key): ?string
    {
        $value = $this->get($key);

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
