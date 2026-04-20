<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\BranchInventory;

class CheckoutController extends Controller
{
    protected function resolveTerminalBranchId(Request $request): int
    {
        $branchId = (int) $request->session()->get('pos_terminal.branch_id');

        if ($branchId < 1) {
            abort(422, 'Terminal is not selected. Please select terminal again.');
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
            $unitPrice = $p->capital; // defaulting to capital if no price column
            $subtotal = $unitPrice * $qty;
            $items[] = [
                'product_id' => $p->id,
                'product_name' => $p->name,
                'unit' => $p->unit,
                'quantity' => $qty,
                'available_quantity' => (float) (optional($inventories->get($p->id))->quantity ?? 0),
                'unit_price' => $unitPrice,
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
// It validates the incoming request data, checks for sufficient stock in the branch inventory,
    public function finalize(Request $request)
    {
        $data = $request->validate([
            'payment_method' => 'required|string|in:cash',
            'payment_details' => 'nullable|array',
        ]);

        $branchId = $this->resolveTerminalBranchId($request);
        $terminal = $request->session()->get('pos_terminal', []);

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
                    abort(422, 'Insufficient stock for product '.$pid);
                }
                $unitPrice = $products[$pid]->capital; // adjust if you have price
                $subtotal = $unitPrice * $qty;
                $total += $subtotal;
                $inv->decrement('quantity', $qty);
            }

            $sale = Sale::create([
                'date' => now(),
                'user_id' => $request->user()->id,
                'total_amount' => $total,
                'branch_id' => $branchId,
                'payment_method' => $data['payment_method'],
            ]);

            foreach ($cart as $c) {
                $p = $products[$c['product_id']];
                $unitPrice = $p->capital;
                $sale->items()->create([
                    'product_id' => $p->id,
                    'quantity' => $c['quantity'],
                    'markup' => 0,
                    'subtotal' => $unitPrice * $c['quantity'],
                ]);
            }

            $receiptItems = [];
            foreach ($cart as $c) {
                $p = $products[$c['product_id']];
                $unitPrice = (float) $p->capital;
                $qty = (float) $c['quantity'];
                $receiptItems[] = [
                    'product_id' => $p->id,
                    'product_name' => $p->name,
                    'unit' => $p->unit,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'subtotal' => $unitPrice * $qty,
                ];
            }

            // clear cart
            $request->session()->forget('pos_cart');

            return response()->json([
                'sale_id' => $sale->id,
                'total' => $total,
                'receipt' => [
                    'sale_id' => $sale->id,
                    'date' => optional($sale->date)->format('Y-m-d H:i:s'),
                    'cashier' => $request->user()->name,
                    'branch_id' => $branchId,
                    'branch_name' => $terminal['branch_name'] ?? null,
                    'terminal_id' => $terminal['terminal_id'] ?? null,
                    'terminal_name' => $terminal['terminal_name'] ?? null,
                    'payment_method' => $sale->payment_method,
                    'items' => $receiptItems,
                    'total' => (float) $total,
                ],
            ]);
        });
    }
}
