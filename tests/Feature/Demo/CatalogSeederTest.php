<?php

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Product;
use Database\Seeders\BranchSeeder;
use Database\Seeders\CatalogSeeder;

beforeEach(function () {
    $this->seed(BranchSeeder::class);
    $this->seed(CatalogSeeder::class);
    $this->hardware = Branch::where('name', "Milaran's Hardware and Motor Parts")->firstOrFail();
    $this->tiles = Branch::where('name', "Milaran's Tiles and Flooring Division")->firstOrFail();
});

test('each branch gets its own catalog', function () {
    expect(BranchInventory::where('branch_id', $this->hardware->id)->count())->toBe(50)
        ->and(BranchInventory::where('branch_id', $this->tiles->id)->count())->toBe(35)
        ->and(Product::count())->toBe(85);

    // No product is stocked at both branches.
    expect(BranchInventory::select('product_id')->groupBy('product_id')->havingRaw('count(*) > 1')->count())->toBe(0);
});

test('products land at the right branch', function () {
    $branchOf = fn (string $name) => BranchInventory::whereHas('product', fn ($q) => $q->where('name', $name))->value('branch_id');

    expect($branchOf('Portland Cement 40 kg'))->toBe($this->hardware->id)
        ->and($branchOf('Drive Chain 428H x 120L'))->toBe($this->hardware->id)
        ->and($branchOf('Tile Adhesive 25 kg'))->toBe($this->tiles->id);
});

test('the catalog includes low and out-of-stock items for the dashboard', function () {
    expect(BranchInventory::where('quantity', '>', 0)->where('quantity', '<=', 5)->count())->toBe(9)
        ->and(BranchInventory::where('quantity', '<=', 0)->count())->toBe(5)
        ->and(BranchInventory::where('status', '!=', 'active')->count())->toBe(0);
});

test('names with inch marks and commas survive the CSV', function () {
    expect(Product::where('name', 'Common Wire Nail 2"')->exists())->toBeTrue()
        ->and(Product::where('name', 'Vinyl Plank 6"x36" Natural Oak (box, 2.6 sqm)')->exists())->toBeTrue()
        ->and((float) Product::where('name', 'Portland Cement 40 kg')->value('capital'))->toBe(255.0);
});
