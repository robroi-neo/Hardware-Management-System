<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request)
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
}
