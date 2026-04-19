<?php

namespace Atldays\Geo\Contracts;

use Atldays\Geo\Data\UpdateOptions;

interface GeoDriverUpdatable
{
    public function update(UpdateOptions $options): UpdateResultContract;
}
