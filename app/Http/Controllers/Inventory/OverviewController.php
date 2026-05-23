<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\BranchInventory;
use App\Models\Branch;
use App\Models\Product;
use Illuminate\Http\Request;

class OverviewController extends Controller
{
    public function index(Request $request)
    {
        // Check if user is admin
        $isAdmin = auth()->user()->hasRole('admin');

        // Get the user's selected branch from session
        $sessionBranch = session('branch');
        $userDefaultBranchId = $sessionBranch['id'] ?? null;

        // Get sort parameters
        $sortBy = $request->query('sort_by', 'date');
        $sortDir = $request->query('sort_dir', 'desc');
        $search = $request->query('search', '');
        $filterBranchId = $request->query('branch_id', null);
        $filterStatus = $request->query('status', null);

        // Whitelist allowed columns
        $allowedColumns = ['name', 'quantity', 'date'];
        if (!in_array($sortBy, $allowedColumns)) {
            $sortBy = 'date';
        }

        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }

        // Determine which branch(es) to display
        $displayBranchId = null;
        if ($isAdmin && $filterBranchId) {
            // Admin selected a specific branch via filter
            $displayBranchId = (int) $filterBranchId;
        } elseif ($userDefaultBranchId) {
            // Use user's terminal branch
            $displayBranchId = $userDefaultBranchId;
        }

        // Build query
        $query = BranchInventory::with(['product.branchInventories', 'branch']);

        // Filter by branch
        if ($displayBranchId) {
            $query->where('branch_id', $displayBranchId);
        }

        // Filter by status (product.status)
        $allowedStatuses = ['active', 'inactive'];
        if ($filterStatus && in_array($filterStatus, $allowedStatuses)) {
            $query->whereHas('product', function ($q) use ($filterStatus) {
                $q->where('status', $filterStatus);
            });
        }

        // Filter by search term
        if (!empty($search)) {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('unit', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        if ($sortBy === 'name') {
            $query->join('products', 'branch_inventory.product_id', '=', 'products.id')
                  ->select('branch_inventory.*')
                  ->orderBy('products.name', $sortDir);
        } elseif ($sortBy === 'quantity') {
            $query->orderBy('quantity', $sortDir);
        } else {
            $query->orderBy('branch_inventory.created_at', $sortDir);
        }

        // 1. CALCULATE TRUE TOTAL VALUE BEFORE PAGINATING
        $totalValue = (clone $query)->get()->sum(function ($inv) {
            return $inv->quantity * ($inv->product->capital ?? 0);
        });

        $lowStockCount = (clone $query)->where('quantity', '<', 5)->count();

        // 2. NOW PAGINATE
        $inventories = $query->paginate(10);

        // Get all branches for dropdown (only used if admin)
        $allBranches = $isAdmin ? Branch::all() : collect();

        // Status options for filter
        $statuses = collect([
            (object)['value' => 'active', 'label' => 'Active'],
            (object)['value' => 'inactive', 'label' => 'Archived'],
        ]);

        return view('modules.inventory.stock-overview', [
            'inventories' => $inventories,
            'totalValue' => $totalValue, // PASS IT TO THE VIEW
            'lowStockCount' => $lowStockCount,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'search' => $search,
            'branchId' => $displayBranchId,
            'filterBranchId' => $filterBranchId,
            'filterStatus' => $filterStatus,
            'isAdmin' => $isAdmin,
            'allBranches' => $allBranches,
            'statuses' => $statuses,
        ]);
    }

    public function archive(Request $request, BranchInventory $inventory)
    {
        // Only check THIS branch inventory quantity
        if ((float) $inventory->quantity > 0) {
            $msg = 'Cannot archive a product while stock quantity is above zero.';

            return $request->wantsJson()
                ? response()->json([
                    'success' => false,
                    'message' => $msg
                ], 400)
                : back()->with('error', $msg);
        }

        // Already archived?
        if ($inventory->status === 'inactive') {
            $msg = 'Product is already archived for this branch.';

            return $request->wantsJson()
                ? response()->json([
                    'success' => false,
                    'message' => $msg
                ], 400)
                : back()->with('info', $msg);
        }

        $inventory->update([
            'status' => 'inactive'
        ]);

        $msg = 'Product archived successfully for this branch.';

        return $request->wantsJson()
            ? response()->json([
                'success' => true,
                'message' => $msg
            ])
            : redirect()
                ->route('inventory.overview')
                ->with('success', $msg);
    }

    public function restore(Request $request, BranchInventory $inventory)
    {
        if ($inventory->status === 'active') {
            $msg = 'Product is already active for this branch.';

            return $request->wantsJson()
                ? response()->json([
                    'success' => false,
                    'message' => $msg
                ], 400)
                : back()->with('info', $msg);
        }

        $inventory->update([
            'status' => 'active'
        ]);

        $msg = 'Product restored successfully for this branch.';

        return $request->wantsJson()
            ? response()->json([
                'success' => true,
                'message' => $msg
            ])
            : redirect()
                ->route('inventory.overview')
                ->with('success', $msg);
    }
}
