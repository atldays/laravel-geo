<?php

namespace Atldays\Geo\Data\Casts;

use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\Creation\CreationContext;
use Spatie\LaravelData\Support\DataProperty;

class LocalizedNameCast implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $properties, CreationContext $context): mixed
    {
        if (is_string($value)) {
            return trim($value);
        }

        if (!is_array($value)) {
            return '';
        }

        $preferred = $value['en'] ?? null;

        if (is_string($preferred) && trim($preferred) !== '') {
            return trim($preferred);
        }

        foreach ($value as $name) {
            if (is_string($name) && trim($name) !== '') {
                return trim($name);
            }
        }

        return '';
    }
}
