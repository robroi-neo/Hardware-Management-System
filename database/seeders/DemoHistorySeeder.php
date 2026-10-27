<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\RefundReason;
use App\Models\Supplier;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * About four weeks of believable history for the demo: daily sales (a few
 * refunded), supplier purchases every few days, stock movements for each,
 * and audit log entries in the same shape the app writes.
 *
 * Stock stays consistent with the movement log: an "opening stock"
 * adjustment starts each product's history, and a "stock count" adjustment
 * on the last day brings it to the level in seeders/data/catalog.csv, so the
 * dashboard still shows the intended low and out-of-stock items.
 *
 * The output is the same every run (fixed random seed) for a given date.
 */
class DemoHistorySeeder extends Seeder
{
    /** Suppliers each branch buys from, by name. */
    private const SUPPLIERS = [
        0 => ['Davao Builders Supply', 'Mindanao Motor Parts Trading', 'Tagum Steel and Cement Center', 'Island Paints Distribution'],
        1 => ['Southern Tiles & Ceramics Depot'],
    ];

    /** Sales per day: [min, max] for each branch (by position). */
    private const SALES_PER_DAY = [0 => [6, 14], 1 => [3, 8]];

    private array $stock = [];      // [branchId][productId] => quantity
    private array $products = [];   // productId => product row
    private array $movements = [];
    private array $audit = [];

    public function run(): void
    {
        mt_srand(20261027);

        $days = (int) config('demo.history_days', 28);
        $now = Carbon::now();
        $branches = Branch::orderBy('id')->get()->values();
        $reasons = RefundReason::where('is_active', true)->pluck('id', 'name');
        $manager = User::where('phone', config('demo.users.manager.phone'))->firstOrFail();
        $admin = User::where('phone', config('demo.users.admin.phone'))->firstOrFail();

        $this->products = DB::table('products')->get()->keyBy('id')->all();
        $targets = []; // final stock wanted, from the catalog

        foreach (DB::table('branch_inventory')->get() as $row) {
            $targets[$row->branch_id][$row->product_id] = (float) $row->quantity;
        }

        DB::transaction(function () use ($days, $now, $branches, $reasons, $manager, $admin, $targets) {
            foreach ($branches as $position => $branch) {
                $catalog = $targets[$branch->id] ?? [];
                if (! $catalog) {
                    continue;
                }

                $cashiers = $this->cashiersFor($branch->id);
                $suppliers = Supplier::whereIn('supplier_name', self::SUPPLIERS[$position] ?? [])->pluck('id')->all()
                    ?: Supplier::where('status', 'active')->pluck('id')->all();
                $start = $now->copy()->subDays($days)->setTime(7, 0);

                // Opening stock: enough to trade on for the first days.
                foreach ($catalog as $productId => $final) {
                    $opening = $this->openingStock($productId, $final);
                    $this->stock[$branch->id][$productId] = $opening;
                    $this->move($branch->id, $productId, $opening, 'adjustment', 'other', null, $admin->id, $start);
                }

                for ($daysAgo = $days - 1; $daysAgo >= 0; $daysAgo--) {
                    $day = $now->copy()->subDays($daysAgo)->startOfDay();

                    // Restock from a supplier every 5 days, in the morning.
                    if ($daysAgo % 5 === 4) {
                        $this->purchase($branch->id, $suppliers[array_rand($suppliers)], $manager->id, $day->copy()->setTime(8, mt_rand(0, 40)));
                    }

                    [$min, $max] = self::SALES_PER_DAY[$position] ?? [3, 8];
                    $times = $this->saleTimes($day, mt_rand($min, $max), $now);

                    foreach ($times as $time) {
                        $saleId = $this->sale($branch->id, $cashiers[array_rand($cashiers)], $time);

                        // About 1 in 30 sales is refunded later that day.
                        if ($saleId && mt_rand(1, 30) === 1 && $reasons->isNotEmpty()) {
                            $refundAt = $time->copy()->addMinutes(mt_rand(20, 180));
                            if ($refundAt->lessThan($now)) {
                                $this->refund($saleId, $manager, $reasons->random(), $refundAt);
                            }
                        }
                    }
                }

                // Stock count on the last day: bring each product to its catalog level.
                $countAt = $now->copy()->subMinutes(30);
                foreach ($catalog as $productId => $final) {
                    $difference = round($final - $this->stock[$branch->id][$productId], 2);
                    if ($difference != 0) {
                        $this->move($branch->id, $productId, $difference, 'adjustment', 'other', null, $admin->id, $countAt);
                        $this->stock[$branch->id][$productId] = $final;
                    }
                }
            }

            foreach (array_chunk($this->movements, 500) as $chunk) {
                DB::table('inventory_movements')->insert($chunk);
            }
            foreach (array_chunk($this->audit, 500) as $chunk) {
                DB::table('audit_logs')->insert($chunk);
            }
        });

        // branch_inventory already holds the catalog levels, which is where history ends.
    }

    /** Branch cashiers, falling back to the demo cashier. */
    private function cashiersFor(int $branchId): array
    {
        $ids = User::role('cashier')->where('branch_id', $branchId)->where('status', 'active')->pluck('id')->all();
        $demo = User::where('phone', config('demo.users.cashier.phone'))->value('id');

        return array_values(array_unique(array_filter([...$ids, $demo])));
    }

    private function openingStock(int $productId, float $final): float
    {
        $unit = $this->products[$productId]->unit;
        $base = match ($unit) {
            'meter' => 300,
            'piece' => (float) $this->products[$productId]->capital > 1000 ? 6 : 40,
            default => 25,
        };

        return max($final, 0) + $base;
    }

    /** Opening-hour sale times for a day, never in the future. */
    private function saleTimes(CarbonInterface $day, int $count, CarbonInterface $now): array
    {
        $times = [];
        for ($i = 0; $i < $count; $i++) {
            $time = $day->copy()->setTime(mt_rand(8, 17), mt_rand(0, 59), mt_rand(0, 59));
            if ($time->lessThan($now)) {
                $times[] = $time;
            }
        }
        sort($times);

        return $times;
    }

    private function quantityFor(object $product): float
    {
        return match ($product->unit) {
            'meter' => mt_rand(5, 30),
            'kg' => mt_rand(1, 5),
            'box' => mt_rand(1, 8),
            'bag', 'length', 'sheet' => mt_rand(1, 10),
            'liter' => mt_rand(1, 4),
            default => (float) $product->capital > 1000 ? 1 : mt_rand(1, 4),
        };
    }

    private function sale(int $branchId, int $cashierId, CarbonInterface $at): ?int
    {
        $available = array_keys(array_filter($this->stock[$branchId], fn ($qty) => $qty >= 1));
        if (! $available) {
            return null;
        }

        shuffle($available);
        $lines = [];
        foreach (array_slice($available, 0, mt_rand(1, 4)) as $productId) {
            $product = $this->products[$productId];
            $qty = min($this->quantityFor($product), floor($this->stock[$branchId][$productId]));
            if ($qty < 1) {
                continue;
            }
            $markup = round((float) $product->capital * mt_rand(10, 30) / 100);
            $lines[] = [$product, $qty, $markup];
        }

        if (! $lines) {
            return null;
        }

        $total = array_sum(array_map(fn ($l) => ((float) $l[0]->capital + $l[2]) * $l[1], $lines));

        $saleId = DB::table('sales')->insertGetId([
            'date' => $at, 'user_id' => $cashierId, 'total_amount' => $total, 'branch_id' => $branchId,
            'payment_method' => 'cash', 'refunded' => false, 'created_at' => $at, 'updated_at' => $at,
        ]);

        foreach ($lines as [$product, $qty, $markup]) {
            $price = (float) $product->capital + $markup;
            DB::table('sales_items')->insert([
                'sale_id' => $saleId, 'product_id' => $product->id, 'quantity' => $qty,
                'unit_price' => $price, 'unit_cost' => (float) $product->capital, 'markup' => $markup,
                'subtotal' => $price * $qty, 'created_at' => $at, 'updated_at' => $at,
            ]);
            $this->stock[$branchId][$product->id] -= $qty;
            $this->move($branchId, $product->id, -$qty, 'out', 'sale', $saleId, $cashierId, $at);
        }

        $this->log($cashierId, 'sale', $saleId, 'created', null, [
            'total_amount' => (float) $total, 'payment_method' => 'cash',
            'items_count' => count($lines), 'branch_id' => $branchId,
        ], $at);

        return $saleId;
    }

    private function refund(int $saleId, User $manager, int $reasonId, CarbonInterface $at): void
    {
        $sale = DB::table('sales')->find($saleId);

        DB::table('sales')->where('id', $saleId)->update([
            'refunded' => true, 'refunded_by' => $manager->id, 'refunded_at' => $at,
            'refund_reason_id' => $reasonId, 'updated_at' => $at,
        ]);

        foreach (DB::table('sales_items')->where('sale_id', $saleId)->get() as $item) {
            $this->stock[$sale->branch_id][$item->product_id] += (float) $item->quantity;
            $this->move($sale->branch_id, $item->product_id, (float) $item->quantity, 'in', 'sale', $saleId, $manager->id, $at);
        }

        $this->log($manager->id, 'sale', $saleId, 'refund',
            ['refunded' => false, 'refunded_by' => null, 'refunded_at' => null, 'refund_reason_id' => null, 'refund_note' => null],
            ['refunded' => true, 'refunded_by' => $manager->name, 'refunded_at' => $at->format('Y-m-d H:i:s'),
                'refund_amount' => (float) $sale->total_amount, 'refund_reason' => RefundReason::find($reasonId)?->name, 'refund_note' => null],
            $at);
    }

    private function purchase(int $branchId, int $supplierId, int $userId, CarbonInterface $at): void
    {
        // Restock what is running low, plus a few random items.
        $low = array_keys(array_filter($this->stock[$branchId], fn ($qty) => $qty < 15));
        $others = array_keys($this->stock[$branchId]);
        shuffle($others);
        $productIds = array_slice(array_values(array_unique([...$low, ...array_slice($others, 0, 4)])), 0, 10);

        if (! $productIds) {
            return;
        }

        $purchaseId = DB::table('purchases')->insertGetId([
            'supplier_id' => $supplierId, 'branch_id' => $branchId, 'date' => $at, 'created_at' => $at, 'updated_at' => $at,
        ]);

        $total = 0;
        foreach ($productIds as $productId) {
            $product = $this->products[$productId];
            $qty = $product->unit === 'meter' ? 200 : ((float) $product->capital > 1000 ? mt_rand(3, 8) : mt_rand(20, 50));
            $subtotal = (float) $product->capital * $qty;
            $total += $subtotal;

            DB::table('purchase_details')->insert([
                'purchase_id' => $purchaseId, 'product_id' => $productId, 'quantity' => $qty,
                'unit_price' => (float) $product->capital, 'subtotal' => $subtotal, 'created_at' => $at, 'updated_at' => $at,
            ]);
            $this->stock[$branchId][$productId] += $qty;
            $this->move($branchId, $productId, $qty, 'in', 'purchase', $purchaseId, $userId, $at);
        }

        $invoiceId = DB::table('invoices')->insertGetId([
            'purchase_id' => $purchaseId, 'date_issued' => $at, 'date_due' => $at->copy()->addDays(30),
            'total_amount' => $total, 'refunded' => false, 'created_at' => $at, 'updated_at' => $at,
        ]);

        $this->log($userId, 'purchase', $purchaseId, 'created', null, [
            'supplier_id' => $supplierId, 'branch_id' => $branchId, 'total_amount' => (float) $total,
            'items_count' => count($productIds), 'invoice_id' => $invoiceId,
        ], $at);
    }

    private function move(int $branchId, int $productId, float $change, string $type, string $referenceType, ?int $referenceId, int $userId, CarbonInterface $at): void
    {
        $this->movements[] = [
            'product_id' => $productId, 'branch_id' => $branchId, 'user_id' => $userId, 'type' => $type,
            'quantity_change' => $change, 'reference_type' => $referenceType, 'reference_id' => $referenceId,
            'created_at' => $at, 'updated_at' => $at,
        ];
    }

    private function log(int $userId, string $entity, int $entityId, string $action, ?array $old, array $new, CarbonInterface $at): void
    {
        $this->audit[] = [
            'user_id' => $userId, 'entity_type' => $entity, 'entity_id' => $entityId, 'action' => $action,
            'old_values' => $old ? json_encode($old) : null, 'new_values' => json_encode($new), 'created_at' => $at,
        ];
    }
}
