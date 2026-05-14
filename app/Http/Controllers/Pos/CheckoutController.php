<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\BranchInventory;
use App\Models\AuditLog;

class CheckoutController extends Controller
{
    protected function resolveTerminalBranchId(Request $request): int
    {
        $branch = $request->session()->get('branch', []);
        $branchId = (int) ($branch['id'] ?? 0);

        if ($branchId < 1) {
            abort(422, 'Branch is not selected. Please select a branch first.');
        }

        return $branchId;
    }

//  Prepare Function - This function is responsible for preparing the checkout data based on the current cart items stored in the session.
//  It retrieves the product details and inventory information for the products in the cart, calculates the subtotal for each item,
//  and returns a JSON response containing the items, total amount, and available payment methods.
    public function prepare(Request $request)
    {
        $branchId = $this->resolveTerminalBranchId($request);
        $cart = $request->session()->get('pos_cart', []);
        $productIds = array_column($cart, 'product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $inventories = BranchInventory::where('branch_id', $branchId)
            ->whereIn('product_id', $productIds)
            ->get()
            ->keyBy('product_id');

        $items = [];
        $total = 0;
        foreach ($cart as $c) {
            $p = $products[$c['product_id']];
            $qty = $c['quantity'];
            $unitPrice = $p->capital;
            $markup = (float) ($c['markup'] ?? 0);
            $sellingPrice = $unitPrice + $markup;
            $subtotal = $sellingPrice * $qty;
            $items[] = [
                'product_id' => $p->id,
                'product_name' => $p->name,
                'unit' => $p->unit,
                'quantity' => $qty,
                'available_quantity' => (float) (optional($inventories->get($p->id))->quantity ?? 0),
                'unit_price' => $unitPrice,
                'markup' => $markup,
                'cost' => $p->capital,
                'subtotal' => $subtotal,
            ];
            $total += $subtotal;
        }

        return response()->json([
            'items' => $items,
            'total' => $total,
            'payment_methods' => ['cash'],
        ]);
    }
//  Finalize Function - This function handles the finalization of the checkout process.
//// It validates the incoming request data, checks for sufficient stock in the branch inventory,
    public function finalize(Request $request)
    {
        $data = $request->validate([
            'payment_method' => 'required|string|in:cash',
            'payment_details' => 'nullable|array',
        ]);

        $branchId = $this->resolveTerminalBranchId($request);
        $terminal = $request->session()->get('branch', []);

        $cart = $request->session()->get('pos_cart', []);
        if (empty($cart)) {
            return response()->json(['message' => 'Cart is empty'], 422);
        }

        $productIds = array_column($cart, 'product_id');

        return DB::transaction(function () use ($request, $cart, $data, $productIds, $branchId, $terminal) {
            $inventories = BranchInventory::where('branch_id', $branchId)
                ->whereIn('product_id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            $total = 0;
            foreach ($cart as $c) {
                $pid = $c['product_id'];
                $qty = $c['quantity'];
                $inv = $inventories[$pid] ?? null;
                if (! $inv || $inv->quantity < $qty) {
                    abort(422, 'Insufficient stock for product ' . $pid);
                }
                $markup = (float) ($c['markup'] ?? 0); // fixed key
                $sellingPrice = $products[$pid]->capital + $markup;
                $total += $sellingPrice * $qty;
                $inv->decrement('quantity', $qty);
            }

            $sale = Sale::create([
                'date'           => now(),
                'user_id'        => $request->user()->id,
                'total_amount'   => $total,
                'branch_id'      => $branchId,
                'payment_method' => $data['payment_method'],
            ]);

            AuditLog::create([
                'user_id'     => $request->user()->id,
                'entity_type' => 'sale',
                'entity_id'   => $sale->id,
                'action'      => 'created',
                'new_values'  => [
                    'total_amount'   => (float) $total,
                    'payment_method' => $sale->payment_method,
                    'items_count'    => count($cart),
                    'branch_id'      => $branchId,
                ],
            ]);

            // Save line items to DB and build receipt
            $receiptItems = [];
            foreach ($cart as $c) {
                $p        = $products[$c['product_id']];
                $markup   = (float) ($c['markup'] ?? 0); // fixed key
                $qty      = (float) $c['quantity'];
                $selling  = $p->capital + $markup;
                $subtotal = $selling * $qty;

                // Persist each line item
                $sale->items()->create([
                    'product_id'    => $p->id,
                    'quantity'      => $qty,
                    'unit_price'    => $selling,
                    'markup' => $markup,
                    'subtotal'      => $subtotal,
                ]);

                $receiptItems[] = [
                    'product_id'   => $p->id,
                    'product_name' => $p->name,
                    'unit'         => $p->unit,
                    'quantity'     => $qty,
                    'unit_price'   => $selling,
                    'subtotal'     => $subtotal,
                ];
            }
            $request->session()->forget('pos_cart');

            return response()->json([
                'sale_id' => $sale->id,
                'total'   => $total,
                'receipt' => [
                    'sale_id'        => $sale->id,
                    'date'           => optional($sale->date)->format('Y-m-d H:i:s'),
                    'cashier'        => $request->user()->name,
                    'branch_id'      => $branchId,
                    'branch_name'    => $terminal['name'] ?? null,
                    'terminal_id'    => null,
                    'terminal_name'  => null,
                    'payment_method' => $sale->payment_method,
                    'items'          => $receiptItems,
                    'total'          => (float) $total,
                ],
            ]);
        });
    }
}
