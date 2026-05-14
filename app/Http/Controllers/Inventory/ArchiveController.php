<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArchiveController extends Controller
{
    /**
     * Display the archive list
     */
    public function index(Request $request)
    {
        $isAdmin = auth()->user()->hasRole('admin');
        $sessionBranch = $request->session()->get('branch');
        $userDefaultBranchId = $sessionBranch['id'] ?? null;

        // Get filter parameters
        $search = $request->query('search', '');
        $sortBy = $request->query('sort_by', 'name');
        $sortDir = $request->query('sort_dir', 'asc');
        $filterBranchId = $request->query('branch_id', null);

        // Whitelist allowed sort columns
        $allowedSortColumns = ['id', 'name', 'unit', 'capital', 'archived_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'archived_at';
        }

        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'asc';
        }

        // Build query for archived products
        $query = Product::archived()
            ->with(['branchInventories.branch']);

        // Apply search
        if (!empty($search)) {
            $query->search($search);
        }

        // Apply sorting
        $query->orderBy($sortBy, $sortDir);

        $archivedProducts = $query->paginate(15);

        // Get branches for filter (admin only)
        $branches = $isAdmin ? \App\Models\Branch::all() : collect();

        return view('modules.inventory.archives', [
            'archivedProducts' => $archivedProducts,
            'search' => $search,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'filterBranchId' => $filterBranchId,
            'isAdmin' => $isAdmin,
            'branches' => $branches,
        ]);
    }

    /**
     * Archive a product
     */
    public function archiveItem(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if ($product->status === 'archived') {
            return response()->json(['message' => 'Product is already archived'], 400);
        }

        DB::transaction(function () use ($product, $request) {
            $oldStatus = $product->status;
            $product->update([
                'status' => 'archived',
                'archived_at' => now(),
            ]);

            // Log the action
            AuditLog::create([
                'user_id' => $request->user()->id,
                'entity_type' => 'product',
                'entity_id' => $product->id,
                'action' => 'archive',
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => 'archived'],
                'created_at' => now(),
            ]);
        });

        return response()->json(['message' => 'Product archived successfully']);
    }

    /**
     * Unarchive a product
     */
    public function unarchiveItem(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if ($product->status === 'active') {
            return response()->json(['message' => 'Product is already active'], 400);
        }

        DB::transaction(function () use ($product, $request) {
            $oldStatus = $product->status;
            $product->update([
                'status' => 'active',
                'archived_at' => null,
            ]);

            // Log the action
            AuditLog::create([
                'user_id' => $request->user()->id,
                'entity_type' => 'product',
                'entity_id' => $product->id,
                'action' => 'unarchive',
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => 'active'],
                'created_at' => now(),
            ]);
        });

        return response()->json(['message' => 'Product unarchived successfully']);
    }

    /**
     * Get archived items (API for frontend)
     */
    public function getArchivedItems(Request $request)
    {
        $search = $request->query('search', '');
        $sortBy = $request->query('sort_by', 'name');
        $sortDir = $request->query('sort_dir', 'asc');
        $filterBranchId = $request->query('branch_id', null);

        // Whitelist allowed sort columns
        $allowedSortColumns = ['id', 'name', 'unit', 'capital', 'archived_at'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'archived_at';
        }

        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'asc';
        }

        $query = Product::archived()
            ->with(['branchInventories.branch']);

        // Apply search
        if (!empty($search)) {
            $query->search($search);
        }

        // Apply branch filter if specified
        if ($filterBranchId) {
            $query->whereHas('branchInventories', function ($q) use ($filterBranchId) {
                $q->where('branch_id', $filterBranchId);
            });
        }

        // Apply sorting
        $query->orderBy($sortBy, $sortDir);

        $products = $query->paginate(15);

        return response()->json($products);
    }
}