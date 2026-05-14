<?php

namespace App\Services\Inventory;

use App\Models\BranchInventory;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductAvailabilityService
{
    public function searchActiveProducts(?string $query, int $limit, ?int $branchId = null): Collection
    {
        $products = Product::query()
            ->search($query)
            ->whereRaw('status = ?', ['active'])
            ->limit($limit)
            ->get(['id', 'name', 'unit', 'capital']);

        return $this->hydrateAvailableQuantity($products, $branchId);
    }

    public function browseActiveProducts(?string $query, int $perPage, ?int $branchId = null): LengthAwarePaginator
    {
        $products = Product::query()
            ->search($query)
            ->whereRaw('status = ?', ['active'])
            ->paginate($perPage, ['id', 'name', 'unit', 'capital']);

        $products->setCollection($this->hydrateAvailableQuantity($products->getCollection(), $branchId));

        return $products;
    }

    private function hydrateAvailableQuantity(Collection $products, ?int $branchId = null): Collection
    {
        if ($products->isEmpty()) {
            return $products;
        }

        $availableByProduct = collect();

        if ($branchId && $branchId > 0) {
            $availableByProduct = BranchInventory::query()
                ->where('branch_id', $branchId)
                ->whereIn('product_id', $products->pluck('id'))
                ->pluck('quantity', 'product_id');
        }

        return $products->map(function ($product) use ($availableByProduct) {
            $product->available_quantity = (float) ($availableByProduct[$product->id] ?? 0);

            return $product;
        });
    }
}

