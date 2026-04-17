<?php

namespace Atldays\Geo\Contracts;

use Atldays\Geo\Data\UpdateOptions;
use Atldays\Geo\Data\UpdateResult;

interface Updatable
{
    public function update(UpdateOptions $options): UpdateResult;
}
