<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Supplier;
use Illuminate\Http\Request;

class InvoiceHistoryController extends Controller
{
    public function index(Request $request)
    {
        $sortBy = $request->query('sort_by', 'date_issued');
        $sortDir = $request->query('sort_dir', 'desc');
        $search = $request->query('search', '');
        $filterSupplierId = $request->query('supplier_id', '');

        // Whitelist allowed columns for sorting
        $allowedColumns = ['id', 'purchase_id', 'date_issued', 'date_due', 'total_amount'];
        if (!in_array($sortBy, $allowedColumns)) {
            $sortBy = 'date_issued';
        }

        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }

        // Build query
        $query = Invoice::with(['purchase.supplier', 'purchase.details']);

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoices.id', 'like', "%{$search}%")
                    ->orWhere('invoices.purchase_id', 'like', "%{$search}%")
                    ->orWhereHas('purchase.supplier', function ($subQ) use ($search) {
                        $subQ->where('supplier_name', 'like', "%{$search}%");
                    });
            });
        }

        // Apply supplier filter
        if ($filterSupplierId) {
            $query->whereHas('purchase', function ($q) use ($filterSupplierId) {
                $q->where('supplier_id', $filterSupplierId);
            });
        }

        // Apply sorting and pagination
        $invoices = $query->orderBy($sortBy, $sortDir)
            ->paginate(15)
            ->appends($request->query());

        // Get all suppliers for filter dropdown
        $suppliers = Supplier::where('status', 'active')->orderBy('supplier_name')->get();

        return view('modules.purchasing.invoice-history', [
            'invoices' => $invoices,
            'suppliers' => $suppliers,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'search' => $search,
            'filterSupplierId' => $filterSupplierId,
        ]);
    }
}

