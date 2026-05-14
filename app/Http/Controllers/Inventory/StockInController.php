<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\BranchInventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockInController extends Controller
{
    /**
     * Show the manual stock-in form
     */
    public function create(Request $request)
    {
        // Get selected branch from session (if assigned)
        $sessionBranch = $request->session()->get('branch');
        $userDefaultBranchId = $sessionBranch['id'] ?? null;

        $isAdmin = auth()->user()->hasRole('admin');
        $branches = $isAdmin ? Branch::all() : collect();

        return view('modules.inventory.manual-stock-in', [
            'isAdmin' => $isAdmin,
            'branches' => $branches,
            'userDefaultBranchId' => $userDefaultBranchId,
        ]);
    }

    /**
     * Search products for stock-in (API endpoint)
     */
    public function searchProducts(Request $request)
    {
        $q = $request->query('q');
        $limit = min(max((int) $request->query('limit', 20), 1), 50);
        $branchId = (int) $request->query('branch_id', 0);

        $productsQuery = Product::query()
            ->search($q)
            ->whereRaw('status = ?', ['active']);

        $products = $productsQuery
            ->limit($limit)
            ->get(['id', 'name', 'unit', 'capital']);

        $availableByProductId = collect();
        if ($branchId > 0 && $products->isNotEmpty()) {
            $availableByProductId = BranchInventory::query()
                ->where('branch_id', $branchId)
                ->whereIn('product_id', $products->pluck('id'))
                ->pluck('quantity', 'product_id');
        }

        $payload = $products->map(function ($product) use ($availableByProductId) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'unit' => $product->unit,
                'capital' => $product->capital,
                'available_quantity' => (float) ($availableByProductId[$product->id] ?? 0),
            ];
        });

        return response()->json($payload);
    }

    /**
     * Store stock-in transaction
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'required|integer|exists:branches,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'reference_type' => 'nullable|in:purchase,other',
            'reference_id' => 'nullable|integer',
            'notes' => 'nullable|string|max:500',
        ]);

        // ---- Permission check ----
        $user = $request->user();
        $isAdmin = $user->hasRole('admin');

        if (!$isAdmin) {
            $sessionBranch = $request->session()->get('branch');
            $sessionBranchId = $sessionBranch['id'] ?? null;

            if (!$sessionBranchId || $sessionBranchId != $data['branch_id']) {
                return response()->json(['message' => 'Unauthorized branch access'], 403);
            }
        }

        try {
            return DB::transaction(function () use ($data, $request, $user) {

                $branchId = $data['branch_id'];
                $referenceType = $data['reference_type'] ?? 'other';
                $referenceId = $data['reference_id'] ?? null;

                $items = collect($data['items'])
                    ->filter(fn ($i) => (float) $i['quantity'] > 0)
                    ->values();

                if ($items->isEmpty()) {
                    return response()->json(['message' => 'No valid items to process'], 422);
                }

                $productIds = $items->pluck('product_id');

                // ---- Preload inventories (1 query instead of N) ----
                $inventories = BranchInventory::whereRaw('branch_id = ?', [$branchId])
                    ->whereIn('product_id', $productIds)
                    ->get()
                    ->keyBy('product_id');

                $movementRows = [];
                $totalItems = 0;

                foreach ($items as $item) {
                    $productId = $item['product_id'];
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

                    // atomic increment (fast)
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

                // ---- batch insert (huge speed boost) ----
                InventoryMovement::insert($movementRows);

                return response()->json([
                    'success' => true,
                    'message' => "Stock-in completed. {$totalItems} units added.",
                    'movements_count' => count($movementRows),
                    'total_quantity' => $totalItems,
                ]);
            });

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error processing stock-in: ' . $e->getMessage(),
            ], 500);
        }
    }
}

