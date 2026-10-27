<?php

use App\Models\Supplier;


// Bug #8: each supplier action must check its own permission.
test('#8 a manager without suppliers.delete cannot delete a supplier', function () {
    $manager = userWithRole('manager');
    $supplier = Supplier::create(supplierPayload());

    $this->actingAs($manager)->delete("/suppliers/{$supplier->id}")->assertForbidden();

    expect(Supplier::find($supplier->id))->not->toBeNull();
});

test('#8 a user without suppliers.create cannot add a supplier', function () {
    $user = userWithRole('cashier');
    $user->givePermissionTo('suppliers.view');

    $this->actingAs($user)->post('/suppliers', supplierPayload())->assertForbidden();

    expect(Supplier::count())->toBe(0);
});

test('#8 a user without suppliers.edit cannot update a supplier', function () {
    $user = userWithRole('cashier');
    $user->givePermissionTo('suppliers.view');
    $supplier = Supplier::create(supplierPayload());

    $this->actingAs($user)->put("/suppliers/{$supplier->id}", supplierPayload(['contact_person' => 'Changed']))
        ->assertForbidden();

    expect($supplier->fresh()->contact_person)->toBe('Juan Dela Cruz');
});

test('#8 a manager can still add and edit suppliers, and an admin can delete', function () {
    $manager = userWithRole('manager');
    $admin = userWithRole('admin');

    $this->actingAs($manager)->post('/suppliers', supplierPayload())->assertRedirect();
    $supplier = Supplier::firstOrFail();

    $this->actingAs($manager)->put("/suppliers/{$supplier->id}", supplierPayload(['contact_person' => 'Maria']))
        ->assertRedirect();
    expect($supplier->fresh()->contact_person)->toBe('Maria');

    $this->actingAs($admin)->delete("/suppliers/{$supplier->id}")->assertRedirect();
    expect(Supplier::find($supplier->id))->toBeNull();
});

// Bug #9: the POS and inventory APIs must check permissions.
test('#9 a user without inventory.update cannot post stock-in or stock-out', function () {
    $cashier = userWithRole('cashier');
    $branch = $cashier->branch;
    $product = \App\Models\Product::create(['name' => 'Hammer', 'capital' => 100, 'unit' => 'piece']);
    \App\Models\BranchInventory::create(['branch_id' => $branch->id, 'product_id' => $product->id, 'quantity' => 10, 'status' => 'active']);
    $payload = ['branch_id' => $branch->id, 'items' => [['product_id' => $product->id, 'quantity' => 5]]];

    $this->actingAs($cashier)->withSession(branchSession($branch))
        ->postJson('/inventory/api/stock-in/store', $payload)->assertForbidden();
    $this->actingAs($cashier)->withSession(branchSession($branch))
        ->postJson('/inventory/api/stock-out/store', $payload)->assertForbidden();
    $this->actingAs($cashier)->withSession(branchSession($branch))
        ->getJson('/inventory/api/products/search?q=Ham&branch_id='.$branch->id)->assertForbidden();

    expect((float) \App\Models\BranchInventory::value('quantity'))->toBe(10.0);
});

test('#9 a manager can still post stock-in', function () {
    $manager = userWithRole('manager');
    $branch = $manager->branch;
    $product = \App\Models\Product::create(['name' => 'Hammer', 'capital' => 100, 'unit' => 'piece']);

    $this->actingAs($manager)->withSession(branchSession($branch))
        ->postJson('/inventory/api/stock-in/store', ['branch_id' => $branch->id, 'items' => [['product_id' => $product->id, 'quantity' => 5]]])
        ->assertOk();
});

test('#9 a user without sales.create cannot use the POS cart or checkout', function () {
    $user = userWithRole('cashier');
    $user->syncRoles([]);
    $branch = $user->branch;

    $this->actingAs($user)->withSession(branchSession($branch))
        ->postJson('/pos/api/cart/add', ['product_id' => 1, 'quantity' => 1])->assertForbidden();
    $this->actingAs($user)->withSession(branchSession($branch))
        ->postJson('/pos/api/checkout/finalize', ['payment_method' => 'cash'])->assertForbidden();
    $this->actingAs($user)->withSession(branchSession($branch))
        ->getJson('/pos/api/products/search?q=a')->assertForbidden();
});

test('#9 a cashier can still search products and use the cart', function () {
    $cashier = userWithRole('cashier');
    $branch = $cashier->branch;
    $product = \App\Models\Product::create(['name' => 'Hammer', 'capital' => 100, 'unit' => 'piece']);
    \App\Models\BranchInventory::create(['branch_id' => $branch->id, 'product_id' => $product->id, 'quantity' => 10, 'status' => 'active']);

    $this->actingAs($cashier)->withSession(branchSession($branch))
        ->getJson('/pos/api/products/search?q=Ham')->assertOk();
    $this->actingAs($cashier)->withSession(branchSession($branch))
        ->postJson('/pos/api/cart/add', ['product_id' => $product->id, 'quantity' => 1])->assertSuccessful();
});

// Bug #10: sale and invoice detail endpoints need the history permissions.
test('#10 a cashier cannot open sale or invoice details; a manager can', function () {
    $cashier = userWithRole('cashier');
    $manager = userWithRole('manager');
    $branch = $cashier->branch;

    $sale = \App\Models\Sale::create(['date' => now(), 'user_id' => $manager->id, 'total_amount' => 100, 'branch_id' => $branch->id, 'payment_method' => 'cash']);
    $supplier = Supplier::create(supplierPayload());
    $purchase = \App\Models\Purchase::create(['supplier_id' => $supplier->id, 'branch_id' => $branch->id, 'date' => now()]);
    $invoice = \App\Models\Invoice::create(['purchase_id' => $purchase->id, 'date_issued' => now(), 'date_due' => now()->addDays(30), 'total_amount' => 100]);

    $this->actingAs($cashier)->getJson("/pos/transactions/{$sale->id}")->assertForbidden();
    $this->actingAs($cashier)->getJson("/purchasing/invoices/{$invoice->id}")->assertForbidden();

    $this->actingAs($manager)->withSession(branchSession($branch))->getJson("/pos/transactions/{$sale->id}")->assertOk();
    $this->actingAs($manager)->withSession(branchSession($branch))->getJson("/purchasing/invoices/{$invoice->id}")->assertOk();
});
