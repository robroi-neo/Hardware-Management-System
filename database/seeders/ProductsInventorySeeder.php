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

        // Ensure at least one branch exists
        $mainBranch = DB::table('branches')->where('name', 'Main Branch')->first();
        if (! $mainBranch) {
            $mainBranchId = DB::table('branches')->insertGetId([
                'name' => 'Main Branch',
                'address' => 'Head Office',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $mainBranchId = $mainBranch->id;
        }

        // Optionally create a second branch
        $storeBranch = DB::table('branches')->where('name', 'Store')->first();
        if (! $storeBranch) {
            $storeBranchId = DB::table('branches')->insertGetId([
                'name' => 'Store',
                'address' => 'Retail Outlet',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $storeBranchId = $storeBranch->id;
        }

        $branchIds = [$mainBranchId, $storeBranchId];

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
    }
}
