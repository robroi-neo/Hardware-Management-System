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
                'name' => 'Main Branch - Manila',
                'address' => '123 Business Street, Makati, Metro Manila',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cebu Branch',
                'address' => '456 Commerce Avenue, Cebu City',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Davao Branch',
                'address' => '789 Trading Plaza, Davao City',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Quezon City Branch',
                'address' => '321 Shopping District, Quezon City',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
