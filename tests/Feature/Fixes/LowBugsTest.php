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

// Bug #21: the audit log "Refunds" filter must find refunds.
test('#21 audit log Refunds filter returns sale and purchase refunds', function () {
    $admin = userWithRole('admin');
    \App\Models\AuditLog::create(['user_id' => $admin->id, 'entity_type' => 'sale', 'entity_id' => 1, 'action' => 'created']);
    \App\Models\AuditLog::create(['user_id' => $admin->id, 'entity_type' => 'sale', 'entity_id' => 1, 'action' => 'refund']);
    \App\Models\AuditLog::create(['user_id' => $admin->id, 'entity_type' => 'purchase', 'entity_id' => 2, 'action' => 'refund']);

    $logs = $this->actingAs($admin)->get('/audit-logs/user-activity?entity_type=refund')->assertOk()->viewData('logs');

    expect($logs->total())->toBe(2)
        ->and(collect($logs->items())->pluck('action')->unique()->all())->toBe(['refund']);
});
