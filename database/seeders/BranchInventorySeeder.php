<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class BranchInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed inventory for branch ID 1 only with all products
        $branchId = 1;

        // Get all products from database
        $products = Product::all();

        $inventory = [];

        foreach ($products as $product) {
            // Generate random quantity for variety (between 10 and 100)
            $quantity = rand(10, 100);

            $inventory[] = [
                'branch_id' => $branchId,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('branch_inventory')->insert($inventory);
    }
}
