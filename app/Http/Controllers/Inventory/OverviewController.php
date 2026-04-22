<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\BranchInventory;
use App\Models\Branch;
use Illuminate\Http\Request;

class OverviewController extends Controller
{
    public function index(Request $request)
    {
        // Check if user is admin
        $isAdmin = auth()->user()->hasRole('admin');

        // Get the user's terminal branch from session
        $terminalId = session('pos_terminal_id');
        $userDefaultBranchId = null;

        if ($terminalId) {
            // Get branch from terminal
            $terminal = \App\Models\PosTerminal::find($terminalId);
            $userDefaultBranchId = $terminal?->branch_id;
        }

        // Get sort parameters
        $sortBy = $request->query('sort_by', 'date');
        $sortDir = $request->query('sort_dir', 'desc');
        $search = $request->query('search', '');
        $filterBranchId = $request->query('branch_id', null);

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
        $query = BranchInventory::with(['product', 'branch']);

        // Filter by branch
        if ($displayBranchId) {
            $query->where('branch_id', $displayBranchId);
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

        $inventories = $query->paginate(15);

        // Get all branches for dropdown (only used if admin)
        $allBranches = $isAdmin ? Branch::all() : collect();

        return view('modules.inventory.stock-overview', [
            'inventories' => $inventories,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'search' => $search,
            'branchId' => $displayBranchId,
            'filterBranchId' => $filterBranchId,
            'isAdmin' => $isAdmin,
            'allBranches' => $allBranches,
        ]);
    }
}

