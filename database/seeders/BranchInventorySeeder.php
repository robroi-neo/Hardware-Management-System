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
        // Branches to seed
        $branchIds = [1, 2];

        // Get all products
        $products = Product::all();

        $inventory = [];

        foreach ($branchIds as $branchId) {
            foreach ($products as $product) {
                $inventory[] = [
                    'branch_id' => $branchId,
                    'product_id' => $product->id,
                    'quantity' => rand(10, 100),
                    'created_at' => now(),
                    'updated_at' => now(),
                    'status'=> "active",
                ];
            }
        }

        DB::table('branch_inventory')->insert($inventory);
    }
}
