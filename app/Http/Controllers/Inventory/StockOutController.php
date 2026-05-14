<?php
namespace App\Http\Controllers\Inventory;
use App\Http\Controllers\Controller;
use App\Models\BranchInventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class StockOutController extends Controller
{
    /**
     * Show the manual stock-out form
     */
    public function create(Request $request)
    {
        // Get selected branch from session (if assigned)
        $sessionBranch = $request->session()->get('branch');
        $userDefaultBranchId = $sessionBranch['id'] ?? null;
        $isAdmin = auth()->user()->hasRole('admin');
        $branches = $isAdmin ? Branch::all() : collect();
        return view('modules.inventory.manual-stock-out', [
            'isAdmin' => $isAdmin,
            'branches' => $branches,
            'userDefaultBranchId' => $userDefaultBranchId,
        ]);
    }
    /**
     * Search products for stock-out (API endpoint)
     */
    public function searchProducts(Request $request)
    {
        $q = $request->query('q');
        $limit = min(max((int) $request->query('limit', 20), 1), 50);
        $productsQuery = Product::query()
            ->search($q)
            ->whereRaw('status = ?', ['active']);
        $products = $productsQuery
            ->limit($limit)
            ->get(['id', 'name', 'unit', 'capital']);
        return response()->json($products);
    }
    /**
     * Store stock-out transaction
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'required|integer|exists:branches,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'reference_type' => 'nullable|in:sale,transfer,adjustment,other',
            'reference_id' => 'nullable|integer',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $isAdmin = $user->hasRole('admin');

        // ---- branch permission check ----
        if (!$isAdmin) {
            $sessionBranch = $request->session()->get('branch');
            $sessionBranchId = $sessionBranch['id'] ?? null;

            if (!$sessionBranchId || $sessionBranchId != $data['branch_id']) {
                return response()->json(['message' => 'Unauthorized branch access'], 403);
            }
        }

        try {
            return DB::transaction(function () use ($data, $user) {

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

                // ---- preload all inventories in 1 query ----
                $inventories = BranchInventory::whereRaw('branch_id = ?', [$branchId])
                    ->whereIn('product_id', $productIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('product_id');

                $movementRows = [];
                $totalItems = 0;

                foreach ($items as $item) {

                    $productId = $item['product_id'];
                    $quantity = (float) $item['quantity'];

                    $inventory = $inventories[$productId] ?? null;

                    // ---- validation checks ----
                    if (!$inventory) {
                        return response()->json([
                            'message' => "Product {$productId} not found in branch inventory"
                        ], 422);
                    }

                    if ($inventory->quantity < $quantity) {
                        return response()->json([
                            'message' => "Insufficient stock for product {$productId}. Available: {$inventory->quantity}"
                        ], 422);
                    }

                    // ---- update in memory + DB ----
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
                    ];

                    $totalItems += $quantity;
                }

                // ---- bulk insert movements ----
                InventoryMovement::insert($movementRows);

                return response()->json([
                    'success' => true,
                    'message' => "Stock-out completed. {$totalItems} units removed.",
                    'movements_count' => count($movementRows),
                    'total_quantity' => $totalItems,
                ]);
            });

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error processing stock-out: ' . $e->getMessage(),
            ], 500);
        }
    }
}
