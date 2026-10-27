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
