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
