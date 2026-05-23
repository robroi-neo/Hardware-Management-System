<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\RefundReason;
use App\Models\Invoice;
use App\Models\Supplier;
use Illuminate\Http\Request;

class InvoicesController extends Controller
{
    public function index(Request $request)
    {
        // Whitelist and sanitize sort parameters
        $allowedColumns = ['id', 'purchase_id', 'date_issued', 'date_due', 'total_amount'];

        $sortBy = in_array($request->query('sort_by'), $allowedColumns)
            ? $request->query('sort_by')
            : 'date_issued';

        $sortDir = in_array($request->query('sort_dir'), ['asc', 'desc'])
            ? $request->query('sort_dir')
            : 'desc';

        $filterBranchId = $request->query('branch_id', null);

        // Build query
        $query = Invoice::with(['purchase.supplier', 'purchase.details', 'refundedBy']);

        // Apply search filter
        if ($search = $request->query('search', '')) {

            $query->where(function ($q) use ($search) {

                // 1. PRIORITY: Exact invoice ID match (fast path)
                if (is_numeric($search)) {
                    $q->orWhere('invoices.id', (int) $search)
                        ->orWhere('invoices.purchase_id', (int) $search);
                }

                // 2. SECONDARY: Supplier name exact-ish match (still structured)
                $q->orWhereHas('purchase.supplier', function ($subQ) use ($search) {
                    $subQ->where('supplier_name', 'like', "{$search}%"); // prefix match (not full wildcard)
                });

                // 3. FALLBACK: Broad match only if needed
                $q->orWhere('invoices.id', 'like', "%{$search}%")
                    ->orWhere('invoices.purchase_id', 'like', "%{$search}%");
            });
        }

        // Apply supplier filter
        if ($filterSupplierId = $request->query('supplier_id', '')) {
            $query->whereHas('purchase', function ($q) use ($filterSupplierId) {
                $q->where('supplier_id', $filterSupplierId);
            });
        }

        // Filter by date range
        if ($dateFrom = $request->query('date_from')) {
            $query->whereDate('date', '>=', $dateFrom);
        }

        if ($dateTo = $request->query('date_to')) {
            $query->whereDate('date', '<=', $dateTo);
        }

        if ($filterBranchId) {
            $query->whereHas('purchase', function ($q) use ($filterBranchId) {
                $q->where('branch_id', $filterBranchId);
            });
        }

        // Apply sorting and pagination
        $invoices = $query->orderBy($sortBy, $sortDir)
            ->paginate(15)
            ->appends(request()->except('page'));  // Exclude 'page' to prevent pagination duplicates

        // Get all suppliers for filter dropdown
        $suppliers = Supplier::where('status', '=', 'active')->orderBy('supplier_name')->get();

        $allBranches = Branch::all();

        return view('modules.purchasing.invoices', [
            'invoices' => $invoices,
            'suppliers' => $suppliers,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'search' => $search,
            'filterSupplierId' => $filterSupplierId,
            'allBranches' => $allBranches,
            'filterBranchId' => $filterBranchId,
            'refundReasons' => RefundReason::where('is_active', true)->get(),
        ]);
    }
}

