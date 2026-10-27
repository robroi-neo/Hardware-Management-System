<?php

use App\Models\Invoice;
use App\Models\Purchase;
use App\Models\Supplier;

function invoiceIssuedOn(string $date, int $branchId): Invoice
{
    $supplier = Supplier::firstOrCreate(['supplier_name' => 'Acme Hardware Supply'], supplierPayload());
    $purchase = Purchase::create(['supplier_id' => $supplier->id, 'branch_id' => $branchId, 'date' => $date]);

    return Invoice::create(['purchase_id' => $purchase->id, 'date_issued' => $date, 'date_due' => $date, 'total_amount' => 100]);
}

// Bug #12: invoice history date filter must use date_issued.
test('#12 invoice history date filters match on date issued', function () {
    $admin = userWithRole('admin');
    $branch = $admin->branch;
    invoiceIssuedOn('2026-10-01 10:00:00', $branch->id);
    invoiceIssuedOn('2026-10-20 10:00:00', $branch->id);

    $count = fn ($query) => $this->actingAs($admin)->withSession(branchSession($branch))
        ->get('/purchasing/invoice-history?'.$query)->assertOk()->viewData('invoices')->total();

    expect($count('date_from=2026-10-10'))->toBe(1)
        ->and($count('date_to=2026-10-10'))->toBe(1)
        ->and($count('date_from=2026-09-01&date_to=2026-10-31'))->toBe(2);
});

/**
 * Ring up a sale through the real POS endpoints. Returns the new Sale.
 */
function ringUpSale($test, \App\Models\User $user, \App\Models\Branch $branch, \App\Models\Product $product, int $qty, float $markup = 0): \App\Models\Sale
{
    $test->actingAs($user)->withSession(branchSession($branch));
    $test->postJson('/pos/api/cart/add', ['product_id' => $product->id, 'quantity' => $qty])->assertSuccessful();
    if ($markup > 0) {
        $test->postJson('/pos/api/cart/markup', ['product_id' => $product->id, 'markup' => $markup])->assertSuccessful();
    }
    $test->postJson('/pos/api/checkout/finalize', ['payment_method' => 'cash'])->assertSuccessful();

    return \App\Models\Sale::latest('id')->firstOrFail();
}

// Bug #13: a past sale must keep the price it was sold at.
test('#13 sale details keep the original unit price and cost after the product cost changes', function () {
    $manager = userWithRole('manager');
    $branch = $manager->branch;
    $product = stockedProduct($branch->id, 20); // capital 100

    $sale = ringUpSale($this, $manager, $branch, $product, 2, 25);

    $product->update(['capital' => 150]); // e.g. a newer purchase at a higher cost

    $item = $this->actingAs($manager)->getJson("/pos/transactions/{$sale->id}")->assertOk()->json('items.0');

    expect((float) $item['unit_price'])->toBe(125.0)
        ->and((float) $item['cost'])->toBe(100.0)
        ->and((float) $item['markup'])->toBe(25.0)
        ->and((float) $item['subtotal'])->toBe(250.0);
});

function invoiceWithLine(int $branchId, \App\Models\Product $product, float $qty): Invoice
{
    $invoice = invoiceIssuedOn(now()->toDateTimeString(), $branchId);
    \App\Models\PurchaseDetail::create([
        'purchase_id' => $invoice->purchase_id, 'product_id' => $product->id,
        'quantity' => $qty, 'unit_price' => 100, 'subtotal' => 100 * $qty,
    ]);

    return $invoice;
}

// Bug #14: refunding a purchase must not push stock below zero.
test('#14 a purchase refund is refused when the items were already sold', function () {
    $manager = userWithRole('manager');
    $branch = $manager->branch;
    $product = stockedProduct($branch->id, 3); // bought 10, 7 already sold
    $invoice = invoiceWithLine($branch->id, $product, 10);
    $reason = \App\Models\RefundReason::create(['name' => 'Damaged item', 'is_active' => true]);

    $this->actingAs($manager)->withSession(branchSession($branch))
        ->postJson("/purchasing/invoices/{$invoice->id}/refund", ['reason_id' => $reason->id])
        ->assertStatus(422)->assertJsonPath('success', false);

    expect(stockAt($branch->id, $product))->toBe(3.0)
        ->and((bool) $invoice->fresh()->refunded)->toBeFalse();
});

test('#14 a purchase refund still works when stock covers it', function () {
    $manager = userWithRole('manager');
    $branch = $manager->branch;
    $product = stockedProduct($branch->id, 12);
    $invoice = invoiceWithLine($branch->id, $product, 10);
    $reason = \App\Models\RefundReason::create(['name' => 'Damaged item', 'is_active' => true]);

    $this->actingAs($manager)->withSession(branchSession($branch))
        ->postJson("/purchasing/invoices/{$invoice->id}/refund", ['reason_id' => $reason->id])
        ->assertOk()->assertJsonPath('success', true);

    expect(stockAt($branch->id, $product))->toBe(2.0)
        ->and((bool) $invoice->fresh()->refunded)->toBeTrue();
});

// Bug #15: product search on stock-in/out must not 500 without a branch.
test('#15 inventory product search falls back to the session branch, or returns 422 without one', function () {
    $admin = userWithRole('admin');
    $branch = $admin->branch;
    stockedProduct($branch->id, 5, 'Hammer');

    $this->actingAs($admin)->getJson('/inventory/api/products/search?q=Ham')->assertStatus(422);

    $this->actingAs($admin)->withSession(branchSession($branch))
        ->getJson('/inventory/api/products/search?q=Ham')
        ->assertOk()->assertJsonPath('0.name', 'Hammer');
});

// Bug #16: every stock change must leave a movement record.
function movementsFor(\App\Models\Product $product): array
{
    return \App\Models\InventoryMovement::where('product_id', $product->id)->orderBy('id')
        ->get(['type', 'quantity_change', 'reference_type', 'reference_id'])
        ->map(fn ($m) => [$m->type, (float) $m->quantity_change, $m->reference_type, $m->reference_id])
        ->all();
}

test('#16 a sale and its refund both write stock movements', function () {
    $manager = userWithRole('manager');
    $branch = $manager->branch;
    $product = stockedProduct($branch->id, 20);
    $reason = \App\Models\RefundReason::create(['name' => 'Customer changed mind', 'is_active' => true]);

    $sale = ringUpSale($this, $manager, $branch, $product, 3);
    $this->postJson("/pos/transactions/{$sale->id}/refund", ['reason_id' => $reason->id])->assertSuccessful();

    expect(movementsFor($product))->toBe([
        ['out', -3.0, 'sale', $sale->id],
        ['in', 3.0, 'sale', $sale->id],
    ])->and(stockAt($branch->id, $product))->toBe(20.0);
});

test('#16 a purchase and its refund both write stock movements', function () {
    $manager = userWithRole('manager');
    $branch = $manager->branch;
    $product = stockedProduct($branch->id, 0);
    $supplier = \App\Models\Supplier::create(supplierPayload());
    $reason = \App\Models\RefundReason::create(['name' => 'Damaged item', 'is_active' => true]);

    $this->actingAs($manager)->withSession(branchSession($branch));
    $this->postJson('/purchasing/api/cart/add', ['product_id' => $product->id, 'quantity' => 8, 'unit_price' => 90])->assertSuccessful();
    $this->postJson('/purchasing/api/checkout/finalize', ['supplier_id' => $supplier->id, 'branch_id' => $branch->id])
        ->assertSuccessful()->assertJsonPath('success', true);

    $purchase = \App\Models\Purchase::latest('id')->firstOrFail();
    $invoice = \App\Models\Invoice::where('purchase_id', $purchase->id)->firstOrFail();
    $this->postJson("/purchasing/invoices/{$invoice->id}/refund", ['reason_id' => $reason->id])->assertOk();

    expect(movementsFor($product))->toBe([
        ['in', 8.0, 'purchase', $purchase->id],
        ['out', -8.0, 'purchase', $purchase->id],
    ])->and(stockAt($branch->id, $product))->toBe(0.0);
});

function saleOn(string $date, int $branchId, float $amount, bool $refunded = false): \App\Models\Sale
{
    return \App\Models\Sale::create([
        'date' => $date, 'user_id' => \App\Models\User::first()->id, 'total_amount' => $amount,
        'branch_id' => $branchId, 'payment_method' => 'cash', 'refunded' => $refunded,
    ]);
}

// Bug #17: dashboard earnings exclude refunds, and "last week" is 7 days.
test('#17 dashboard earnings leave out refunded sales and count exactly 7 days', function () {
    $this->travelTo(now()->setDate(2026, 10, 27)->setTime(15, 0));
    $admin = userWithRole('admin');
    $branch = $admin->branch;

    saleOn('2026-10-27 09:00:00', $branch->id, 100);          // today
    saleOn('2026-10-27 10:00:00', $branch->id, 999, true);    // today, refunded
    saleOn('2026-10-21 09:00:00', $branch->id, 50);           // 7th day back (in)
    saleOn('2026-10-20 09:00:00', $branch->id, 70);           // 8th day back (out)

    $response = $this->actingAs($admin)->withSession(branchSession($branch))->get('/dashboard')->assertOk();

    expect((float) $response->viewData('earnings_today'))->toBe(100.0)
        ->and((float) $response->viewData('earnings_last_week'))->toBe(150.0)
        ->and($response->viewData('transactions_today'))->toBe(1);
});

// Bug #18: dashboard figures follow the selected branch.
test('#18 dashboard shows only the session branch for non-admins; admins can pick', function () {
    $this->travelTo(now()->setDate(2026, 10, 27)->setTime(15, 0));
    $hardware = makeBranch('Hardware');
    $tiles = makeBranch('Tiles');
    $manager = userWithRole('manager', ['branch_id' => $hardware->id]);
    $manager->givePermissionTo('dashboard.view');
    $admin = userWithRole('admin', ['branch_id' => $hardware->id]);

    saleOn('2026-10-27 09:00:00', $hardware->id, 100);
    saleOn('2026-10-27 09:30:00', $tiles->id, 40);
    stockedProduct($hardware->id, 0, 'Hammer');   // out of stock at Hardware only
    stockedProduct($tiles->id, 50, 'Tile');

    $mine = $this->actingAs($manager)->withSession(branchSession($hardware))->get('/dashboard?branch_id='.$tiles->id);
    expect((float) $mine->viewData('earnings_today'))->toBe(100.0)
        ->and($mine->viewData('out_of_stock_items'))->toBe(1);

    $all = $this->actingAs($admin)->withSession(branchSession($hardware))->get('/dashboard');
    expect((float) $all->viewData('earnings_today'))->toBe(140.0);

    $picked = $this->actingAs($admin)->withSession(branchSession($hardware))->get('/dashboard?branch_id='.$tiles->id);
    expect((float) $picked->viewData('earnings_today'))->toBe(40.0)
        ->and($picked->viewData('out_of_stock_items'))->toBe(0);
});

test('#18 the dashboard renders the branch filter for admins only', function () {
    $hardware = makeBranch('Hardware');
    makeBranch('Tiles');
    $admin = userWithRole('admin', ['branch_id' => $hardware->id]);
    $manager = userWithRole('manager', ['branch_id' => $hardware->id]);
    $manager->givePermissionTo('dashboard.view');

    $this->actingAs($admin)->withSession(branchSession($hardware))->get('/dashboard')
        ->assertOk()->assertSee('Filter by Branch');
    $this->actingAs($manager)->withSession(branchSession($hardware))->get('/dashboard')
        ->assertOk()->assertDontSee('Filter by Branch');
});

// Bug #19: POS browse must hide products archived at the current branch.
test('#19 POS browse hides products archived at this branch even if active elsewhere', function () {
    $hardware = makeBranch('Hardware');
    $tiles = makeBranch('Tiles');
    $cashier = userWithRole('cashier', ['branch_id' => $hardware->id]);

    $archivedHere = \App\Models\Product::create(['name' => 'Old Drill', 'capital' => 100, 'unit' => 'piece']);
    \App\Models\BranchInventory::create(['branch_id' => $hardware->id, 'product_id' => $archivedHere->id, 'quantity' => 0, 'status' => 'inactive']);
    \App\Models\BranchInventory::create(['branch_id' => $tiles->id, 'product_id' => $archivedHere->id, 'quantity' => 5, 'status' => 'active']);
    stockedProduct($hardware->id, 5, 'Hammer');

    $names = collect($this->actingAs($cashier)->withSession(branchSession($hardware))
        ->getJson('/pos/api/products/browse')->assertOk()->json('data'))->pluck('name');

    expect($names->all())->toContain('Hammer')->not->toContain('Old Drill');
});
