<?php

namespace App\Services\Pos;

use App\Models\AuditLog;
use App\Models\BranchInventory;
use App\Models\RefundReason;
use App\Models\Sale;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RefundService
{
    /**
     * Refund a sale: enforce window, restore inventory, persist reason & note, and log audit.
     *
     * @throws \RuntimeException on business rule violation
     */
    public function refund(Sale $sale, int $reasonId, ?string $note, $actorId)
    {
        if ($sale->refunded) {
            throw new \RuntimeException('This transaction has already been refunded.');
        }

        $windowDays = config('refunds.window_days', 7);
        $cutoff = Carbon::parse($sale->date)->addDays($windowDays);

        if (now()->greaterThan($cutoff)) {
            throw new \RuntimeException("Refund window expired (allowed within {$windowDays} days).");
        }

        // reason_id is required by controllers; validate it exists and is active here as well
        $reason = RefundReason::where('is_active', true)->find($reasonId);
        if (! $reason) {
            throw new ModelNotFoundException('Requested refund reason not found or inactive.');
        }

        DB::transaction(function () use ($sale, $reason, $note, $actorId) {
            // Lock the sale and its items
            $saleToRefund = Sale::lockForUpdate()->find($sale->id);

            if ($saleToRefund->refunded) {
                throw new \RuntimeException('This transaction has already been refunded.');
            }

            // Restore inventory for each sale item
            foreach ($saleToRefund->items as $item) {
                $inventory = BranchInventory::lockForUpdate()
                    ->where('branch_id', $saleToRefund->branch_id)
                    ->where('product_id', $item->product_id)
                    ->first();

                if ($inventory) {
                    $inventory->increment('quantity', $item->quantity);
                } else {
                    BranchInventory::create([
                        'branch_id' => $saleToRefund->branch_id,
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                    ]);
                }
            }

            $oldValues = [
                'refunded' => false,
                'refunded_by' => null,
                'refunded_at' => null,
                'refund_reason_id' => null,
                'refund_note' => null,
            ];

            $saleToRefund->update([
                'refunded' => true,
                'refunded_by' => $actorId,
                'refunded_at' => now(),
                'refund_reason_id' => $reason?->id,
                'refund_note' => $note,
            ]);

            // Log to audit
            AuditLog::create([
                'user_id' => $actorId,
                'entity_type' => 'sale',
                'entity_id' => $saleToRefund->id,
                'action' => 'refund',
                'old_values' => $oldValues,
                'new_values' => [
                    'refunded' => true,
                    'refunded_by' => auth()->user()?->name ?? null,
                    'refunded_at' => now()->format('Y-m-d H:i:s'),
                    'refund_amount' => $saleToRefund->total_amount,
                    'refund_reason' => $reason?->name,
                    'refund_note' => $note,
                ],
                'created_at' => now(),
            ]);
        });
    }
}

