<?php

namespace Atldays\Geo\Drivers;

use Atldays\Geo\Contracts\Updatable;
use Atldays\Geo\Data\UpdateOptions;
use Atldays\Geo\Data\UpdateResult;
use Atldays\Geo\Updaters\MaxMindUpdater;

class MaxMind extends AbstractDriver implements Updatable
{
    public function __construct(
        protected MaxMindUpdater $updater,
    ) {}

    public function update(UpdateOptions $options): UpdateResult
    {
        return $this->updater->update($options);
    }
}
