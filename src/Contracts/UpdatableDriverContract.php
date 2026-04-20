<?php

namespace Atldays\Geo\Contracts;

use Atldays\Geo\Data\UpdateOptions;

interface UpdatableDriverContract
{
    public function update(UpdateOptions $options): UpdateResultContract;
}
