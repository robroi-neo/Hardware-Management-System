<?php

namespace App\Console\Commands;

use App\Services\Demo\DemoReset;
use App\Support\DemoState;
use Illuminate\Console\Command;

class DemoResetCommand extends Command
{
    protected $signature = 'demo:reset
        {--if-due : Only reset when the reset interval has passed (for the scheduler)}
        {--build : Build the snapshot first if it does not exist}';

    protected $description = 'Put the demo database back to the clean snapshot';

    public function handle(DemoReset $reset): int
    {
        if ($this->option('if-due') && ! DemoState::isDue()) {
            return self::SUCCESS;
        }

        if (! $reset->snapshotExists()) {
            if (! $this->option('build')) {
                $this->error('No demo snapshot yet. Run php artisan demo:snapshot first (or pass --build).');

                return self::FAILURE;
            }
            $reset->buildSnapshot();
        }

        if (! $reset->restore()) {
            $this->error('Nothing was reset: the database is not a SQLite file, or another reset is running.');

            return self::FAILURE;
        }

        $this->info('Demo data reset. Next reset: '.DemoState::nextResetAt()->toDateTimeString());

        return self::SUCCESS;
    }
}
