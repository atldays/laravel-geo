<?php

namespace Atldays\Geo\Contracts;

use Atldays\Geo\Data\{UpdateOptions, UpdateResult};

interface GeoDriverUpdatable
{
    public function update(UpdateOptions $options): UpdateResult;
}
