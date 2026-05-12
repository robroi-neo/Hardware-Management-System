<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request, SaleItem $saleItem)
    {
        // Whitelist and sanitize sort parameters
        $allowedColumns = ['id', 'date', 'total_amount', 'payment_method'];

        $sortBy  = in_array($request->query('sort_by'), $allowedColumns)
            ? $request->query('sort_by')
            : 'date';

        $sortDir = in_array($request->query('sort_dir'), ['asc', 'desc'])
            ? $request->query('sort_dir')
            : 'desc';

        $query = Sale::with(['user']);

        // Search by ID or cashier name
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                // Match exact transaction ID if the search is numeric
                if (is_numeric($search)) {
                    $q->where('id', (int) $search);
                }
                // Also search by cashier name
                $q->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter by payment method
        if ($paymentMethod = $request->query('payment_method')) {
            $query->where('payment_method', $paymentMethod);
        }

        // Filter by date range
        if ($dateFrom = $request->query('date_from')) {
            $query->whereDate('date', '>=', $dateFrom);
        }

        if ($dateTo = $request->query('date_to')) {
            $query->whereDate('date', '<=', $dateTo);
        }

        // Apply sorting and paginate
        $transactions = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate(8)
            ->withQueryString(); // preserves all filters + sort in pagination links

        return view('modules.pos.transactions', [
            'transactions' => $transactions,
            'sortBy'       => $sortBy,
            'sortDir'      => $sortDir,
        ]);
    }

    public function show(Sale $sale)
    {
        $sale->load('items.product');
        logger($sale);

        return response()->json([
            'id'             => $sale->id,
            'date'           => $sale->date->format('Y-m-d H:i:s'),
            'payment_method' => ucfirst($sale->payment_method),
            'cashier'        => $sale->user->name ?? 'N/A',
            'branch_name'    => $sale->branch->name ?? ('Branch #' . $sale->branch_id),
            'total_amount'   => (float) $sale->total_amount,
            'items'          => $sale->items->map(fn ($item) => [
                'product_id'   => $item->product_id,
                'product_name' => $item->product->name,
                'unit'         => $item->product->unit,
                'quantity'     => (float) $item->quantity,
                'cost'         => (float) $item->product->capital,
                'markup'       => (float) $item->markup,
                'unit_price'   => (float) ($item->product->capital + $item->markup),
                'subtotal'     => (float) $item->subtotal,
            ]),
        ]);
    }
}
