<?php

// Bug #20: the stock overview "Archived" filter must use branch_inventory.status.
test('#20 stock overview status filter matches the branch inventory status', function () {
    $admin = userWithRole('admin');
    $branch = $admin->branch;
    $active = stockedProduct($branch->id, 5, 'Hammer');
    $archived = \App\Models\Product::create(['name' => 'Old Drill', 'capital' => 100, 'unit' => 'piece']);
    \App\Models\BranchInventory::create(['branch_id' => $branch->id, 'product_id' => $archived->id, 'quantity' => 0, 'status' => 'inactive']);

    $names = fn ($status) => collect($this->actingAs($admin)->withSession(branchSession($branch))
        ->get('/inventory/overview?status='.$status)->assertOk()->viewData('inventories')->items())
        ->map(fn ($row) => $row->product->name)->all();

    expect($names('inactive'))->toBe(['Old Drill'])
        ->and($names('active'))->toBe(['Hammer']);
});
