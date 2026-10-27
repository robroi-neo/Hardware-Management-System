<?php

namespace App\Console\Commands;

use App\Services\Demo\DemoReset;
use Illuminate\Console\Command;

class DemoSnapshot extends Command
{
    protected $signature = 'demo:snapshot';

    protected $description = 'Build the clean demo database snapshot (migrate:fresh --seed into a separate file)';

    public function handle(DemoReset $reset): int
    {
        $this->info('Building the demo snapshot...');
        $reset->buildSnapshot();
        $this->info('Snapshot saved to '.$reset->snapshotPath());

        return self::SUCCESS;
    }
}
