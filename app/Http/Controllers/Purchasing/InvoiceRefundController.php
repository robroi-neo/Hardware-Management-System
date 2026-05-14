<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BranchInventory;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class InvoiceRefundController extends Controller
{
    public function store(Invoice $invoice): JsonResponse
    {
        if ($invoice->refunded) {
            return response()->json([
                'success' => false,
                'message' => 'This invoice has already been refunded.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($invoice) {
                $invoiceToRefund = Invoice::with(['purchase.details', 'refundedBy'])
                    ->whereKey($invoice->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($invoiceToRefund->refunded) {
                    throw new \RuntimeException('This invoice has already been refunded.');
                }

                $purchase = $invoiceToRefund->purchase;

                if (! $purchase) {
                    throw new \RuntimeException('Associated purchase record not found for this invoice.');
                }

                foreach ($purchase->details as $detail) {
                    $inventory = BranchInventory::where('branch_id', '=', $purchase->branch_id)
                        ->where('product_id', '=', $detail->product_id)
                        ->lockForUpdate()
                        ->first();

                    if (! $inventory) {
                        $inventory = BranchInventory::create([
                            'branch_id' => $purchase->branch_id,
                            'product_id' => $detail->product_id,
                            'quantity' => 0,
                        ]);
                    }

                    $inventory->decrement('quantity', $detail->quantity);
                }

                $oldValues = [
                    'refunded' => false,
                    'refunded_by' => null,
                    'refunded_at' => null,
                ];

                $invoiceToRefund->update([
                    'refunded' => true,
                    'refunded_by' => auth()->id(),
                    'refunded_at' => now(),
                ]);

                AuditLog::create([
                    'user_id' => auth()->id(),
                    'entity_type' => 'purchase',  // Changed from 'invoice' to 'purchase'
                    'entity_id' => $purchase->id,  // Log the purchase ID, not invoice ID
                    'action' => 'refund',
                    'old_values' => $oldValues,
                    'new_values' => [
                        'refunded' => true,
                        'refunded_by' => auth()->user()->name,
                        'refunded_at' => now()->format('Y-m-d H:i:s'),
                        'refund_amount' => $invoiceToRefund->total_amount,
                        'purchase_id' => $purchase->id,
                        'branch_id' => $purchase->branch_id,
                        'items_count' => $purchase->details->count(),
                    ],
                ]);

            });

            return response()->json([
                'success' => true,
                'message' => 'Invoice refunded successfully. Inventory has been restored.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing invoice refund: ' . $e->getMessage(),
            ], 500);
        }
    }
}

