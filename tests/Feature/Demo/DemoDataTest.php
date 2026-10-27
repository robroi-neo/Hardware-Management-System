<?php

use App\Models\AuditLog;
use App\Models\Invoice;
use App\Models\Sale;
use App\Models\User;
use App\Support\Demo;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->travelTo(now()->setDate(2026, 10, 27)->setTime(14, 0));
    $this->seed(DatabaseSeeder::class);
});

test('one demo user per role, plus a few named staff and no random accounts', function () {
    foreach (Demo::ROLES as $role) {
        $user = Demo::user($role);
        expect($user)->not->toBeNull()
            ->and($user->hasRole($role))->toBeTrue()
            ->and($user->status)->toBe('active')
            ->and(Demo::isDemoUser($user))->toBeTrue();
    }

    expect(User::count())->toBe(7)
        ->and(Demo::isDemoUser(User::where('name', 'Ana Reyes')->first()))->toBeFalse();
});

test('four weeks of sales at both branches, including today and a few refunds', function () {
    $days = Sale::selectRaw('date(date) as d')->distinct()->pluck('d');

    expect($days->count())->toBe(28)
        ->and(Sale::distinct()->count('branch_id'))->toBe(2)
        ->and(Sale::whereDate('date', today())->count())->toBeGreaterThan(0)
        ->and(Sale::where('refunded', true)->count())->toBeGreaterThan(0)
        ->and(AuditLog::where('action', 'refund')->count())->toBe(Sale::where('refunded', true)->count());
});

test('purchases have invoices and audit entries', function () {
    expect(Invoice::count())->toBeGreaterThan(0)
        ->and(Invoice::count())->toBe(DB::table('purchases')->count())
        ->and(AuditLog::where('entity_type', 'purchase')->where('action', 'created')->count())->toBe(Invoice::count());
});

test('stock matches the movement log and ends at the catalog levels', function () {
    $mismatched = DB::table('branch_inventory as b')
        ->whereRaw('abs(b.quantity - (select coalesce(sum(m.quantity_change), 0) from inventory_movements m where m.product_id = b.product_id and m.branch_id = b.branch_id)) > 0.001')
        ->count();

    expect($mismatched)->toBe(0)
        ->and(DB::table('branch_inventory')->where('quantity', '<', 0)->count())->toBe(0)
        ->and(DB::table('branch_inventory')->where('quantity', '<=', 0)->count())->toBe(5)
        ->and(DB::table('branch_inventory')->where('quantity', '>', 0)->where('quantity', '<=', 5)->count())->toBe(9);
});

test('sale lines store the price they were sold at', function () {
    $item = DB::table('sales_items')->first();

    expect((float) $item->unit_price)->toBe(round((float) $item->subtotal / (float) $item->quantity, 2))
        ->and((float) $item->unit_cost + (float) $item->markup)->toBe((float) $item->unit_price);
});
