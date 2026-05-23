<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\BranchInventory;
use App\Models\Invoice;
use App\Models\RefundReason;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class InvoiceRefundController extends Controller
{
    public function store(Request $request, Invoice $invoice): JsonResponse
    {
        $validated = $request->validate([
            'reason_id' => ['required', 'integer', 'exists:refund_reasons,id'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($invoice->refunded) {
            return response()->json([
                'success' => false,
                'message' => 'This invoice has already been refunded.',
            ], 422);
        }

        try {
            $windowDays = config('refunds.window_days', 7);
            $cutoff = \Illuminate\Support\Carbon::parse($invoice->date_issued)->addDays($windowDays);

            if (now()->greaterThan($cutoff)) {
                return response()->json([
                    'success' => false,
                    'message' => "Refund window expired (allowed within {$windowDays} days).",
                ], 422);
            }

            $reason = null;
            if (! empty($validated['reason_id'])) {
                $reason = RefundReason::where('is_active', true)->find($validated['reason_id']);
                if (! $reason) {
                    throw new ModelNotFoundException('Requested refund reason not found or inactive.');
                }
            }

            DB::transaction(function () use ($invoice, $reason, $validated) {
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
                    $inventory = BranchInventory::where([
                        'branch_id' => $purchase->branch_id,
                        'product_id' => $detail->product_id,
                    ])->lockForUpdate()->first();

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
                    'refund_reason_id' => null,
                    'refund_note' => null,
                ];

                $invoiceToRefund->update([
                    'refunded' => true,
                    'refunded_by' => auth()->id(),
                    'refunded_at' => now(),
                    'refund_reason_id' => $reason?->id,
                    'refund_note' => $validated['note'] ?? null,
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
                        'refund_reason' => $reason?->name,
                        'refund_note' => $validated['note'] ?? null,
                    ],
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Invoice refunded successfully. Inventory has been restored.',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Invoice refund error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing invoice refund: ' . $e->getMessage(),
            ], 500);
        }
    }
}

