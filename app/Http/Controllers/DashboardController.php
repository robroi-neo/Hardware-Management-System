<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\BranchInventory;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Earnings
        $now = Carbon::now();
        $startOfToday = $now->copy()->startOfDay();
        $startOfWeekAgo = $now->copy()->subDays(7)->startOfDay();

        // Sum of total_amount for last 7 days (including today)
        $earnings_last_week = Sale::whereBetween('date', [$startOfWeekAgo, $now])->sum('total_amount');

        // Earnings today
        $earnings_today = Sale::where('date', '>=', $startOfToday)->sum('total_amount');

        // Transactions today (non-refunded)
        $transactions_today = Sale::where('date', '>=', $startOfToday)
            ->where(function ($q) {
                // If refunded column exists, exclude refunded sales
                $q->whereNull('refunded')->orWhere('refunded', false);
            })->count();

        // Inventory overview
        // Total in-stock quantity across all branches
        $in_stock_items = (float) BranchInventory::sum('quantity');

        // Low stock items: entries with small quantity (threshold 5)
        $low_stock_items = BranchInventory::where('quantity', '<=', 5)
            ->where('quantity', '>', 0)->count();

        // Out of stock items
        $out_of_stock_items = BranchInventory::where('quantity', '<=', 0)->count();

        // Weekly transactions count and revenue per day (last 7 days)
        $days = collect();
        $transactions_by_day = [];
        $revenue_by_day = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i)->startOfDay();
            $dayEnd = $day->copy()->endOfDay();
            $label = $day->format('M j');
            $days->push($label);

            $transactions_count = Sale::whereBetween('date', [$day, $dayEnd])
                ->where(function ($q) {
                    $q->whereNull('refunded')->orWhere('refunded', false);
                })->count();

            $dayRevenue = Sale::whereBetween('date', [$day, $dayEnd])->sum('total_amount');

            $transactions_by_day[] = $transactions_count;
            $revenue_by_day[] = (float) $dayRevenue;
        }

        return view('modules.dashboard', [
            'earnings_last_week' => $earnings_last_week,
            'earnings_today' => $earnings_today,
            'transactions_today' => $transactions_today,
            'in_stock_items' => $in_stock_items,
            'low_stock_items' => $low_stock_items,
            'out_of_stock_items' => $out_of_stock_items,
            'weekly_labels' => $days->toArray(),
            'weekly_transactions' => $transactions_by_day,
            'weekly_revenue' => $revenue_by_day,
        ]);
    }
}
