<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Sale;
use App\Services\Inventory\BranchContextService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly BranchContextService $branchContextService,
    ) {}

    public function index(Request $request)
    {
        // Non-admins always see their session branch. Admins see all branches
        // unless they pick one, the same as the inventory pages.
        $isAdmin = $request->user()->hasRole('admin');
        $branchId = $isAdmin
            ? ((int) $request->query('branch_id', 0) ?: null)
            : $this->branchContextService->selectedBranchId($request);

        // Sales that count toward earnings: not refunded, in the chosen branch.
        $sales = fn () => Sale::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where(fn ($q) => $q->whereNull('refunded')->orWhere('refunded', false));

        $inventory = fn () => BranchInventory::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $now = Carbon::now();
        $startOfToday = $now->copy()->startOfDay();
        // Last 7 days including today.
        $startOfWeek = $now->copy()->subDays(6)->startOfDay();

        $earnings_last_week = $sales()->whereBetween('date', [$startOfWeek, $now])->sum('total_amount');
        $earnings_today = $sales()->where('date', '>=', $startOfToday)->sum('total_amount');
        $transactions_today = $sales()->where('date', '>=', $startOfToday)->count();

        $in_stock_items = (float) $inventory()->sum('quantity');
        $low_stock_items = $inventory()->where('quantity', '<=', 5)->where('quantity', '>', 0)->count();
        $out_of_stock_items = $inventory()->where('quantity', '<=', 0)->count();

        $days = [];
        $transactions_by_day = [];
        $revenue_by_day = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i)->startOfDay();
            $dayEnd = $day->copy()->endOfDay();

            $days[] = $day->format('M j');
            $transactions_by_day[] = $sales()->whereBetween('date', [$day, $dayEnd])->count();
            $revenue_by_day[] = (float) $sales()->whereBetween('date', [$day, $dayEnd])->sum('total_amount');
        }

        return view('modules.dashboard', [
            'earnings_last_week' => $earnings_last_week,
            'earnings_today' => $earnings_today,
            'transactions_today' => $transactions_today,
            'in_stock_items' => $in_stock_items,
            'low_stock_items' => $low_stock_items,
            'out_of_stock_items' => $out_of_stock_items,
            'weekly_labels' => $days,
            'weekly_transactions' => $transactions_by_day,
            'weekly_revenue' => $revenue_by_day,
            'isAdmin' => $isAdmin,
            'branches' => $isAdmin ? Branch::all() : collect(),
            'filterBranchId' => $branchId,
        ]);
    }
}
