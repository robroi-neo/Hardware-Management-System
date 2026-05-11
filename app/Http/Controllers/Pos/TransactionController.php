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
        // Get sort parameters from query string
        $sortBy = $request->query('sort_by', 'date');
        $sortDir = $request->query('sort_dir', 'desc');

        // Whitelist allowed columns for security
        $allowedColumns = ['id', 'date', 'total_amount', 'payment_method'];
        if (!in_array($sortBy, $allowedColumns)) {
            $sortBy = 'date';
        }

        // Whitelist direction
        if (!in_array($sortDir, ['asc', 'desc'])) {
            $sortDir = 'desc';
        }

        $query = Sale::with(['user']);

        // Apply sorting
        $query->orderBy($sortBy, $sortDir);

        $transactions = $query->paginate(8);

        return view('modules.pos.transactions', [
            'transactions' => $transactions,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
        ]);
    }

    public function show(Sale $sale)
    {

        $sale->load('items.product');
        logger($sale);
        return response()->json([
            'id' => $sale->id,
            'date' => $sale->date->format('Y-m-d H:i:s'),
            'payment_method' => ucfirst($sale->payment_method),
            'cashier' => $sale->user->name ?? 'N/A',
            'branch_name' => $sale->branch->name ?? ('Branch #' . $sale->branch_id),
            'total_amount' => (float) $sale->total_amount,
            'items' => $sale->items->map(fn($item) => [
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
