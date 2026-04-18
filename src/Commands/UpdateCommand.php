<?php

namespace Atldays\Geo\Commands;

use Atldays\Geo\Contracts\GeoDriverUpdatable;
use Atldays\Geo\Data\UpdateOptions;
use Atldays\Geo\GeoManager;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Throwable;

class UpdateCommand extends Command
{
    protected $signature = 'geo:update
        {--force : Download even if the local resource appears up to date}';

    protected $description = 'Update all configured drivers that support refreshes.';

    /**
     * @throws BindingResolutionException
     */
    public function handle(GeoManager $manager): int
    {
        $updatableDrivers = 0;

        foreach ($manager->drivers() as $driver) {
            if (!$driver instanceof GeoDriverUpdatable) {
                continue;
            }

            $updatableDrivers++;

            $this->components->info(sprintf('Updating [%s]...', $driver::class));

            try {
                $result = $driver->update(new UpdateOptions(
                    force: (bool)$this->option('force'),
                ));
            } catch (Throwable $e) {
                $this->components->error($e->getMessage());

                return self::FAILURE;
            }

            if (!$result->downloaded) {
                $this->line(sprintf('Already current: %s', $result->databasePath));
                $this->newLine();

                continue;
            }

            $this->line(sprintf('Stored database at %s', $result->databasePath));

            if (!empty($result->remoteLastModified)) {
                $this->line(sprintf('Release date: %s', $result->remoteLastModified));
            }

            $this->newLine();
        }

        if ($updatableDrivers === 0) {
            $this->components->warn('No updatable drivers are configured.');

            return self::SUCCESS;
        }

        $this->components->info('All updatable drivers have been processed.');

        return self::SUCCESS;
    }
}
