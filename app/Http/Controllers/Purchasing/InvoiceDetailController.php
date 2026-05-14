<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Invoice;

class InvoiceDetailController extends Controller
{
    public function show(Invoice $invoice)
    {
        $invoice->load('purchase.supplier', 'purchase.details.product', 'refundedBy:id,name');

        return response()->json([
            'id'             => $invoice->id,
            'purchase_id'    => $invoice->purchase_id,
            'date_issued'    => $invoice->date_issued->format('Y-m-d'),
            'date_due'       => $invoice->date_due->format('Y-m-d'),
            'total_amount'   => (float) $invoice->total_amount,
            'supplier_name'  => $invoice->purchase->supplier->supplier_name,
            'branch_name'    => $invoice->purchase->branch->name,
            'refunded'       => (bool) $invoice->refunded,
            'refunded_at'    => $invoice->refunded_at ? $invoice->refunded_at->format('M d, Y H:i') : null,
            'refunded_by'    => $invoice->refundedBy?->name ?? null,
            'items'          => $invoice->purchase->details->map(fn ($detail) => [
                'product_id'   => $detail->product_id,
                'product_name' => $detail->product->name,
                'unit'         => $detail->product->unit,
                'quantity'     => (float) $detail->quantity,
                'unit_price'   => (float) $detail->unit_price,
                'subtotal'     => (float) $detail->subtotal,
            ]),
        ]);
    }
}

