<?php

namespace Atldays\Geo\Drivers;

use Atldays\Geo\Exceptions\DriverUnavailableException;
use Illuminate\Http\Client\{ConnectionException, PendingRequest};
use Illuminate\Support\Facades\Http;
use JsonException;
use Stringable;
use Throwable;

abstract class HttpDriver extends AbstractDriver
{
    abstract protected function url(): string|Stringable;

    protected function http(): PendingRequest
    {
        return Http::acceptJson();
    }

    protected function fetch(): array
    {
        try {
            $response = $this->http()->get((string)$this->url());
        } catch (ConnectionException $exception) {
            throw DriverUnavailableException::because(
                static::class,
                'The remote HTTP service could not be reached.',
                $exception,
            );
        } catch (Throwable $exception) {
            throw DriverUnavailableException::because(
                static::class,
                'The remote HTTP request failed unexpectedly.',
                $exception,
            );
        }

        if (!$response->successful()) {
            throw DriverUnavailableException::because(
                static::class,
                sprintf('The remote HTTP service returned an unsuccessful response (%s).', $response->status()),
            );
        }

        try {
            $payload = $response->json(flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw DriverUnavailableException::because(
                static::class,
                'The remote HTTP service returned invalid JSON.',
                $exception,
            );
        }

        if (!is_array($payload)) {
            throw DriverUnavailableException::because(
                static::class,
                'The remote HTTP service returned an unexpected JSON payload.',
            );
        }

        return $payload;
    }
}
