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
        // DB enum allows: active, inactive. We treat 'inactive' as Archived in the UI.
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

        $inventories = $query->paginate(10);

        // Get all branches for dropdown (only used if admin)
        $allBranches = $isAdmin ? Branch::all() : collect();

        // Status options for filter. Map UI label 'Archived' to DB value 'inactive'.
        $statuses = collect([
            (object)['value' => 'active', 'label' => 'Active'],
            (object)['value' => 'inactive', 'label' => 'Archived'],
        ]);

        return view('modules.inventory.stock-overview', [
            'inventories' => $inventories,
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

    public function archive(Product $product)
    {
        // Do not archive if any inventory still has stock.
        $totalQuantity = (float) $product->branchInventories()->sum('quantity');
        if ($totalQuantity > 0) {
            return back()->with('error', 'Cannot archive a product while stock quantity is above zero.');
        }

        // Mark product as archived by setting DB-allowed status 'inactive'.
        if ($product->status === 'inactive') {
            return back()->with('info', 'Product is already archived.');
        }

        $product->update(['status' => 'inactive']);

        return redirect()->route('inventory.overview')->with('success', 'Product archived successfully.');
    }

    public function restore(Product $product)
    {
        // Only restore if product is currently inactive
        if ($product->status === 'active') {
            return back()->with('info', 'Product is already active.');
        }

        $product->update(['status' => 'active']);

        return redirect()->route('inventory.overview')->with('success', 'Product restored successfully.');
    }
}
