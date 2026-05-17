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
                'name' => "Milaran's Hardware and Motor Parts",
                'address' => 'Provincial Rd, Mawab, Davao de Oro',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => "Milaran's Tiles and Flooring Division",
                'address' => 'Provincial Rd, Mawab, Davao de Oro',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
