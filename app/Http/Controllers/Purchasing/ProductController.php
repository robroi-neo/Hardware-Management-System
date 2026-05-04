<?php

namespace App\Http\Controllers\Purchasing;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductController extends Controller
{
    /**
     * Standardize product name to UPPERCASE_WITH_UNDERSCORES format
     *
     * @param string $name
     * @return string
     */
    protected function standardizeName(string $name): string
    {
        // Convert to uppercase
        $standardized = strtoupper(trim($name));

        // Replace non-alphanumeric characters (except underscores) with underscores
        $standardized = preg_replace('/[^A-Z0-9_]/', '_', $standardized);

        // Collapse multiple consecutive underscores
        $standardized = preg_replace('/_+/', '_', $standardized);

        // Remove leading/trailing underscores
        $standardized = trim($standardized, '_');

        return $standardized;
    }

    /**
     * Validate product name uniqueness (case-insensitive)
     *
     * @param string $name
     * @param int|null $excludeId - Product ID to exclude from check (for updates)
     * @return bool
     */
    protected function validateUniqueness(string $name, ?int $excludeId = null): bool
    {
        $standardized = $this->standardizeName($name);

        $query = Product::whereRaw('UPPER(name) = ?', [strtoupper($standardized)]);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    /**
     * Search for products by name or unit
     * Returns: product_id, name (standardized), unit, capital (cost price)
     */
    public function search(Request $request)
    {
        $term = $request->query('q', '');

        if (empty($term)) {
            return response()->json([
                'data' => [],
            ]);
        }

        $products = Product::search($term)
            ->select('id', 'name', 'unit', 'capital')
            ->limit(10)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'unit' => $product->unit,
                    'capital' => $product->capital,
                ];
            });

        return response()->json([
            'data' => $products,
        ]);
    }

    /**
     * Create a new product (inline during purchasing)
     * Required fields: name, unit, capital
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'capital' => 'required|numeric|min:0.01',
        ]);

        try {
            // Standardize the product name
            $standardized = $this->standardizeName($validated['name']);

            // Check uniqueness (case-insensitive)
            if (!$this->validateUniqueness($standardized)) {
                throw ValidationException::withMessages([
                    'name' => "Product '{$validated['name']}' already exists (standardized as: {$standardized})",
                ]);
            }

            // Create product
            $product = Product::create([
                'name' => $standardized,
                'unit' => $validated['unit'],
                'capital' => (float) $validated['capital'],
                'status' => 'active',
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'unit' => $product->unit,
                    'capital' => $product->capital,
                ],
                'message' => "Product '{$standardized}' created successfully",
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Preview standardized name (for inline modal)
     * User types name, system shows what it will be standardized to
     */
    public function preview(Request $request)
    {
        $name = $request->query('name', '');

        if (empty($name)) {
            return response()->json(['standardized' => '', 'exists' => false]);
        }

        $standardized = $this->standardizeName($name);
        $exists = !$this->validateUniqueness($standardized);

        return response()->json([
            'standardized' => $standardized,
            'exists' => $exists,
        ]);
    }
}

