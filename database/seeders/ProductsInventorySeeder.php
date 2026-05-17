<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ProductsInventorySeeder extends Seeder
{
    /**
     * Run the seeder.
     */
    public function run(): void
    {
        $faker = Faker::create();

            // Ensure branches exist and use the canonical branch names defined in BranchSeeder
        $primaryName = "Milaran's Hardware and Motor Parts";
        $secondaryName = "Milaran's Tiles and Flooring Division";

        $primary = DB::table('branches')->where('name', $primaryName)->first();
        if (! $primary) {
            $primaryId = DB::table('branches')->insertGetId([
                'name' => $primaryName,
                'address' => 'Provincial Rd, Mawab, Davao de Oro',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $primaryId = $primary->id;
        }

        $secondary = DB::table('branches')->where('name', $secondaryName)->first();
        if (! $secondary) {
            $secondaryId = DB::table('branches')->insertGetId([
                'name' => $secondaryName,
                'address' => 'Provincial Rd, Mawab, Davao de Oro',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $secondaryId = $secondary->id;
        }

        $branchIds = [$primaryId, $secondaryId];

        // Create sample products
        $count = 50;
        for ($i = 1; $i <= $count; $i++) {
            $name = $faker->words(2, true) . ' ' . $i;
            $capital = $faker->randomFloat(2, 10, 500);

            $productId = DB::table('products')->insertGetId([
                'name' => ucfirst($name),
                'capital' => $capital,
                'unit' => 'pcs',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create inventory rows for each branch
            foreach ($branchIds as $branchId) {
                $quantity = rand(5, 200);
                DB::table('branch_inventory')->insertOrIgnore([
                    'branch_id' => $branchId,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Also create a few tile/flooring-specific products and add inventory only to the Tiles branch
        $tileProducts = [
            'CERAMIC_TILE_30X30',
            'PORCELAIN_TILE_60X60',
            'GROUT_SAND',
            'ADHESIVE_CEMENT',
            'VINYL_TILE_STANDARD',
        ];

        foreach ($tileProducts as $slug) {
            $name = str_replace('_', ' ', ucfirst(strtolower($slug)));
            $capital = $faker->randomFloat(2, 20, 200);

            $productId = DB::table('products')->insertGetId([
                'name' => $name,
                'capital' => $capital,
                'unit' => 'pcs',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Only add inventory for the Tiles & Flooring Division (secondary branch)
            DB::table('branch_inventory')->insertOrIgnore([
                'branch_id' => $secondaryId,
                'product_id' => $productId,
                'quantity' => rand(10, 150),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
