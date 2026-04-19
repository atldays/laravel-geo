<?php

namespace Atldays\Geo\Data;

use Spatie\LaravelData\Attributes\{MapInputName, WithCast};
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
class IpApiConfig extends Data
{
    public function __construct(
        public string $baseUrl,
        #[WithCast(Casts\EmptyStringToNullCast::class)]
        public ?int $timeout = null,
    ) {}
}
