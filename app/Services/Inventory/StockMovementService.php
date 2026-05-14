<?php

namespace App\Services\Inventory;

use App\Models\BranchInventory;
use App\Models\InventoryMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StockMovementService
{
    public function storeStockIn(int $branchId, array $items, User $user, ?string $referenceType = 'other', ?int $referenceId = null): array
    {
        return DB::transaction(function () use ($branchId, $items, $user, $referenceType, $referenceId) {
            $items = collect($items)
                ->filter(fn ($item) => (float) ($item['quantity'] ?? 0) > 0)
                ->values();

            if ($items->isEmpty()) {
                return [
                    'success' => false,
                    'status' => 422,
                    'message' => 'No valid items to process',
                ];
            }

            $productIds = $items->pluck('product_id');
            $inventories = BranchInventory::query()
                ->where('branch_id', $branchId)
                ->whereIn('product_id', $productIds)
                ->get()
                ->keyBy('product_id');

            $movementRows = [];
            $totalItems = 0;

            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $quantity = (float) $item['quantity'];

                $inventory = $inventories[$productId] ?? null;

                if (!$inventory) {
                    $inventory = BranchInventory::create([
                        'branch_id' => $branchId,
                        'product_id' => $productId,
                        'quantity' => 0,
                    ]);

                    $inventories[$productId] = $inventory;
                }

                $inventory->increment('quantity', $quantity);

                $movementRows[] = [
                    'product_id' => $productId,
                    'branch_id' => $branchId,
                    'user_id' => $user->id,
                    'type' => 'in',
                    'quantity_change' => $quantity,
                    'reference_type' => $referenceType,
                    'reference_id' => $referenceId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $totalItems += $quantity;
            }

            InventoryMovement::insert($movementRows);

            return [
                'success' => true,
                'message' => "Stock-in completed. {$totalItems} units added.",
                'movements_count' => count($movementRows),
                'total_quantity' => $totalItems,
            ];
        });
    }

    public function storeStockOut(int $branchId, array $items, User $user, ?string $referenceType = 'other', ?int $referenceId = null): array
    {
        return DB::transaction(function () use ($branchId, $items, $user, $referenceType, $referenceId) {
            $items = collect($items)
                ->filter(fn ($item) => (float) ($item['quantity'] ?? 0) > 0)
                ->values();

            if ($items->isEmpty()) {
                return [
                    'success' => false,
                    'status' => 422,
                    'message' => 'No valid items to process',
                ];
            }

            $productIds = $items->pluck('product_id');
            $inventories = BranchInventory::query()
                ->where('branch_id', $branchId)
                ->whereIn('product_id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            $movementRows = [];
            $totalItems = 0;

            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $quantity = (float) $item['quantity'];

                $inventory = $inventories[$productId] ?? null;

                if (!$inventory) {
                    return [
                        'success' => false,
                        'status' => 422,
                        'message' => "Product {$productId} not found in branch inventory",
                    ];
                }

                if ($inventory->quantity < $quantity) {
                    return [
                        'success' => false,
                        'status' => 422,
                        'message' => "Insufficient stock for product {$productId}. Available: {$inventory->quantity}",
                    ];
                }

                $inventory->decrement('quantity', $quantity);

                $movementRows[] = [
                    'product_id' => $productId,
                    'branch_id' => $branchId,
                    'user_id' => $user->id,
                    'type' => 'out',
                    'quantity_change' => -$quantity,
                    'reference_type' => $referenceType,
                    'reference_id' => $referenceId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $totalItems += $quantity;
            }

            InventoryMovement::insert($movementRows);

            return [
                'success' => true,
                'message' => "Stock-out completed. {$totalItems} units removed.",
                'movements_count' => count($movementRows),
                'total_quantity' => $totalItems,
            ];
        });
    }
}

