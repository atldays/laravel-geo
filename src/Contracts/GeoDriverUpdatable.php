<?php

namespace Atldays\Geo\Contracts;

use Atldays\Geo\Data\UpdateOptions;
use Atldays\Geo\Data\UpdateResult;

interface GeoDriverUpdatable
{
    public function update(UpdateOptions $options): UpdateResult;
}
