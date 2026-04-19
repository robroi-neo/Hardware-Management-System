<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'name' => 'Hammer',
                'capital' => 150.00,
                'unit' => 'piece',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Saw',
                'capital' => 450.00,
                'unit' => 'piece',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Screwdriver Set',
                'capital' => 280.00,
                'unit' => 'set',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Wrench',
                'capital' => 320.00,
                'unit' => 'piece',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Drill Bit Set',
                'capital' => 550.00,
                'unit' => 'set',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Measuring Tape',
                'capital' => 120.00,
                'unit' => 'piece',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Wood Nails Box',
                'capital' => 80.00,
                'unit' => 'box',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Iron Bolts Pack',
                'capital' => 200.00,
                'unit' => 'pack',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pliers',
                'capital' => 180.00,
                'unit' => 'piece',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Clamp Set',
                'capital' => 420.00,
                'unit' => 'set',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
