<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\PosTerminal;
use Illuminate\Database\Seeder;

class PosTerminalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nextTerminalId = 1001;

        Branch::query()->orderBy('id')->get()->each(function (Branch $branch) use (&$nextTerminalId) {
            PosTerminal::firstOrCreate(
                ['terminal_id' => $nextTerminalId],
                [
                    'terminal_name' => 'Terminal '.$branch->id.'-A',
                    'branch_id' => $branch->id,
                ]
            );

            $nextTerminalId++;
        });
    }
}

