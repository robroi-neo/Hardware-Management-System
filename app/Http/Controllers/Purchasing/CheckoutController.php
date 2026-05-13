<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Invoice;
use App\Models\BranchInventory;
use App\Models\Branch;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    public function current(Request $request)
    {
        $cart = $request->session()->get('purchasing_cart', []);

        if (empty($cart)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'items' => [],
                    'total' => 0,
                ],
            ]);
        }

        $productIds = array_column($cart, 'product_id');

        $products = Product::whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        $items = [];
        $total = 0;

        foreach ($cart as $cartItem) {
            $product = $products->get($cartItem['product_id']);

            // Skip deleted products silently
            if (!$product) {
                continue;
            }

            $quantity = (float) $cartItem['quantity'];
            $unitPrice = (float) $cartItem['unit_price'];
            $subtotal = $quantity * $unitPrice;

            $items[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'unit' => $product->unit,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ];

            $total += $subtotal;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'total' => $total,
            ],
        ]);
    }
    /**
     * Prepare checkout: hydrate cart with product details and calculate totals
     */
    public function prepare(Request $request)
    {
        $cart = $request->session()->get('purchasing_cart', []);

        if (empty($cart)) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty',
            ], 422);
        }

        foreach ($cart as $cartItem) {
            $product = $products->get($cartItem['product_id']);

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => "Product #{$cartItem['product_id']} not found",
                ], 422);
            }

            if ($cartItem['quantity'] <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Invalid quantity for {$product->name}",
                ], 422);
            }
        }
        return response()->json([
            'success' => false,
            'message' => 'Checkout preparation failed: ' . $e->getMessage(),
        ], 500);
    }

    /**
     * Finalize checkout: create Purchase, PurchaseDetails, Invoice, and increment inventory
     *
     * Expected payload:
     * - supplier_id: required
     * - branch_id: required
     * - date_due_offset: optional (days from today, default 30)
     */
    public function finalize(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'      => 'required|integer|exists:suppliers,id',
            'branch_id'        => 'required|integer|exists:branches,id',
            'date_due_offset'  => 'nullable|integer|min:0|max:365',
            'due_date'         => 'nullable|date|after_or_equal:today',
        ]);

        try {
            $cart = $request->session()->get('purchasing_cart', []);

            if (empty($cart)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cart is empty',
                ], 422);
            }

            $today = Carbon::now();

            if (!empty($validated['due_date'])) {
                $dateDue = Carbon::parse($validated['due_date']);
            } else {
                $dateDueOffset = $validated['date_due_offset'] ?? 30;
                $dateDue = $today->copy()->addDays($dateDueOffset);
            }

            return DB::transaction(function () use ($cart, $validated, $today, $dateDue, $request) {
                $productIds = array_column($cart, 'product_id');
                $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

                // Create Purchase record
                $purchase = Purchase::create([
                    'supplier_id' => $validated['supplier_id'],
                    'branch_id' => $validated['branch_id'],
                    'date' => $today,
                ]);

                $totalAmount = 0;

                // Create PurchaseDetail records and handle new products
                foreach ($cart as $cartItem) {
                    $product = $products->get($cartItem['product_id']);

                    if (!$product) {
                        throw new \Exception("Product #{$cartItem['product_id']} not found");
                    }

                    $quantity = $cartItem['quantity'];
                    $unitPrice = $cartItem['unit_price'];
                    $subtotal = $unitPrice * $quantity;

                    $product->update([
                        'capital' => $unitPrice
                    ]);
                    // Create purchase detail
                    PurchaseDetail::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'subtotal' => $subtotal,
                    ]);

                    // Increment branch inventory
                    $inventory = BranchInventory::firstOrCreate(
                        [
                            'branch_id' => $validated['branch_id'],
                            'product_id' => $product->id,
                        ],
                        ['quantity' => 0]
                    );

                    $inventory->increment('quantity', $quantity);

                    $totalAmount += $subtotal;
                }

                // Create Invoice record
                $invoice = Invoice::create([
                    'purchase_id' => $purchase->id,
                    'date_issued' => $today,
                    'total_amount' => $totalAmount,
                    'date_due' => $dateDue,
                ]);

                AuditLog::create([
                    'user_id' => $request->user()->id,
                    'entity_type' => 'purchase',
                    'entity_id' => $purchase->id,
                    'action' => 'created',
                    'new_values' => [
                        'supplier_id' => $validated['supplier_id'],
                        'branch_id' => $validated['branch_id'],
                        'total_amount' => (float) $totalAmount,
                        'items_count' => count($cart),
                        'invoice_id' => $invoice->id,
                    ],
                ]);

                // Clear cart
                $request->session()->forget('purchasing_cart');
                $request->session()->forget('purchasing_supplier_id');

                return response()->json([
                    'success' => true,
                    'data' => [
                        'purchase_id' => $purchase->id,
                        'invoice_id' => $invoice->id,
                        'total_amount' => $totalAmount,
                        'items_count' => count($cart),
                        'date_issued' => $today->toDateString(),
                        'date_due' => $dateDue->toDateString(),
                    ],
                    'message' => "Purchase #{$purchase->id} and Invoice #{$invoice->id} created successfully",
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Checkout failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
