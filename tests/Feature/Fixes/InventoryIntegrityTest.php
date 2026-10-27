<?php

use App\Models\BranchInventory;
use App\Models\InventoryMovement;
use App\Models\Product;



// Bug #5: a failed stock-out must not change any stock.
test('#5 a stock-out with one short item changes nothing', function () {
    $admin = userWithRole('admin');
    $branch = $admin->branch;
    $ok = stockedProduct($branch->id, 104, 'Hammer');
    $short = stockedProduct($branch->id, 10, 'Saw');

    $this->actingAs($admin)->withSession(branchSession($branch))
        ->postJson('/inventory/api/stock-out/store', [
            'branch_id' => $branch->id,
            'items' => [
                ['product_id' => $ok->id, 'quantity' => 3],
                ['product_id' => $short->id, 'quantity' => 99999],
            ],
        ])->assertStatus(422)->assertJsonPath('success', false);

    expect(stockAt($branch->id, $ok))->toBe(104.0)
        ->and(stockAt($branch->id, $short))->toBe(10.0)
        ->and(InventoryMovement::count())->toBe(0);
});

test('#5 the same product listed twice is checked against its combined quantity', function () {
    $admin = userWithRole('admin');
    $branch = $admin->branch;
    $product = stockedProduct($branch->id, 10);

    $this->actingAs($admin)->withSession(branchSession($branch))
        ->postJson('/inventory/api/stock-out/store', [
            'branch_id' => $branch->id,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 6],
                ['product_id' => $product->id, 'quantity' => 6],
            ],
        ])->assertStatus(422);

    expect(stockAt($branch->id, $product))->toBe(10.0);
});

test('#5 a valid stock-out still removes stock and logs movements', function () {
    $admin = userWithRole('admin');
    $branch = $admin->branch;
    $a = stockedProduct($branch->id, 10, 'Hammer');
    $b = stockedProduct($branch->id, 5, 'Saw');

    $this->actingAs($admin)->withSession(branchSession($branch))
        ->postJson('/inventory/api/stock-out/store', [
            'branch_id' => $branch->id,
            'items' => [
                ['product_id' => $a->id, 'quantity' => 4],
                ['product_id' => $b->id, 'quantity' => 5],
            ],
        ])->assertOk()->assertJsonPath('success', true);

    expect(stockAt($branch->id, $a))->toBe(6.0)
        ->and(stockAt($branch->id, $b))->toBe(0.0)
        ->and(InventoryMovement::where('type', 'out')->count())->toBe(2);
});
