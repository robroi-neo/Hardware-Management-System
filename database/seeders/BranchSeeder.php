<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('branches')->insert([
            [
                'name' => "Hardware & Motor Parts Branch",
                'address' => 'Sample St, Davao de Oro',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Tiles & Flooring Branch",
                'address' => 'Sample St, Davao de Oro',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
