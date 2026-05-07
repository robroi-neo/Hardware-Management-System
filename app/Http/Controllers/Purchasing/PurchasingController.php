<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class PurchasingController extends Controller
{
    /**
     * Get current cart from session
     */
    public function getCart(Request $request)
    {
        $cart = $request->session()->get('purchasing_cart', []);

        return response()->json([
            'cart' => $cart,
        ]);
    }

    /**
     * Add item to cart (or update quantity if already exists)
     * Expected payload: product_id, quantity, unit_price (optional, defaults to product.capital)
     */
    public function addItem(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'nullable|numeric|min:0',
        ]);

        try {
            $product = Product::find($validated['product_id']);
            $unitPrice = $validated['unit_price'] ?? $product->capital;

            $cart = $request->session()->get('purchasing_cart', []);

            // Check if product already in cart
            $existingIndex = null;
            foreach ($cart as $index => $item) {
                if ($item['product_id'] == $validated['product_id']) {
                    $existingIndex = $index;
                    break;
                }
            }

            if ($existingIndex !== null) {
                // Update existing item
                $cart[$existingIndex]['quantity'] = (float) $validated['quantity'];
                $cart[$existingIndex]['unit_price'] = (float) $unitPrice;
            } else {
                // Add new item
                $cart[] = [
                    'product_id' => $validated['product_id'],
                    'quantity' => (float) $validated['quantity'],
                    'unit_price' => (float) $unitPrice,
                ];
            }

            $request->session()->put('purchasing_cart', $cart);

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart',
                'cart' => $cart,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add item: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update item in cart (quantity or unit_price)
     */
    public function updateItem(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'nullable|numeric|min:0.01',
            'unit_price' => 'nullable|numeric|min:0',
        ]);

        try {
            $cart = $request->session()->get('purchasing_cart', []);

            foreach ($cart as $index => $item) {
                if ($item['product_id'] == $validated['product_id']) {
                    if (isset($validated['quantity'])) {
                        $cart[$index]['quantity'] = (float) $validated['quantity'];
                    }
                    if (isset($validated['unit_price'])) {
                        $cart[$index]['unit_price'] = (float) $validated['unit_price'];
                    }
                    break;
                }
            }

            $request->session()->put('purchasing_cart', $cart);

            return response()->json([
                'success' => true,
                'message' => 'Item updated',
                'cart' => $cart,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update item: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove item from cart
     */
    public function removeItem(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer',
        ]);

        try {
            $cart = $request->session()->get('purchasing_cart', []);

            $cart = array_filter($cart, function ($item) use ($validated) {
                return $item['product_id'] != $validated['product_id'];
            });

            // Re-index array
            $cart = array_values($cart);

            $request->session()->put('purchasing_cart', $cart);

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart',
                'cart' => $cart,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove item: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear entire cart
     */
    public function clearCart(Request $request)
    {
        $validated = $request->validate([
            'clear_supplier' => 'nullable|boolean',
        ]);

        $request->session()->forget('purchasing_cart');

        if ($validated['clear_supplier'] ?? true) {
            $request->session()->forget('purchasing_supplier_id');
        }

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared',
        ]);
    }

    /**
     * Get selected supplier from session
     */
    public function getSelectedSupplier(Request $request)
    {
        return response()->json([
            'success' => true,
            'supplier_id' => $request->session()->get('purchasing_supplier_id'),
        ]);
    }

    /**
     * Set or clear selected supplier in session
     * Expected payload: supplier_id (nullable)
     */
    public function setSelectedSupplier(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
        ]);

        if (array_key_exists('supplier_id', $validated) && $validated['supplier_id']) {
            $request->session()->put('purchasing_supplier_id', $validated['supplier_id']);
        } else {
            $request->session()->forget('purchasing_supplier_id');
        }

        return response()->json([
            'success' => true,
            'supplier_id' => $request->session()->get('purchasing_supplier_id'),
        ]);
    }
}
