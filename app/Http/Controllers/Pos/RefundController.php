<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BranchInventory;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    public function store(Sale $sale)
    {
        // Check if already refunded
        if ($sale->refunded) {
            return response()->json([
                'success' => false,
                'message' => 'This transaction has already been refunded.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($sale) {
                // Lock the sale record for atomicity
                $saleToRefund = Sale::lockForUpdate()->find($sale->id);

                if ($saleToRefund->refunded) {
                    throw new \Exception('This transaction has already been refunded.');
                }

                // Restore inventory for each sale item
                foreach ($saleToRefund->items as $item) {
                    $inventory = BranchInventory::lockForUpdate()
                        ->where('branch_id', $saleToRefund->branch_id)
                        ->where('product_id', $item->product_id)
                        ->first();

                    if ($inventory) {
                        // Increment inventory back (undo the original decrement)
                        $inventory->increment('quantity', $item->quantity);
                    } else {
                        // If entry doesn't exist, create it (shouldn't happen but handle gracefully)
                        BranchInventory::create([
                            'branch_id' => $saleToRefund->branch_id,
                            'product_id' => $item->product_id,
                            'quantity' => $item->quantity,
                        ]);
                    }
                }

                // Mark sale as refunded
                $oldValues = [
                    'refunded' => false,
                    'refunded_by' => null,
                    'refunded_at' => null,
                ];

                $saleToRefund->update([
                    'refunded' => true,
                    'refunded_by' => auth()->id(),
                    'refunded_at' => now(),
                ]);

                // Log to audit
                AuditLog::create([
                    'user_id' => auth()->id(),
                    'entity_type' => 'sale',
                    'entity_id' => $saleToRefund->id,
                    'action' => 'refund',
                    'old_values' => $oldValues,
                    'new_values' => [
                        'refunded' => true,
                        'refunded_by' => auth()->user()->name,
                        'refunded_at' => now()->format('Y-m-d H:i:s'),
                        'refund_amount' => $saleToRefund->total_amount,
                    ],
                    'created_at' => now(),
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Transaction refunded successfully. Inventory has been restored.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing refund: ' . $e->getMessage(),
            ], 500);
        }
    }
}

