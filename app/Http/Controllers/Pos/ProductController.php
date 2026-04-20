<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\BranchInventory;

class ProductController extends Controller
{
    protected function resolveTerminalBranchId(Request $request): int
    {
        $branchId = (int) $request->session()->get('pos_terminal.branch_id');

        if ($branchId < 1) {
            abort(422, 'Terminal is not selected. Please select terminal again.');
        }

        return $branchId;
    }

    public function search(Request $request)
    {
        // get query string
        $q = $request->query('q');
        $limit = min(max((int) $request->query('limit', 20), 1), 50);

        $branchId = $this->resolveTerminalBranchId($request);

        // search all active products, limit to q if there is a query string
        $productsQuery = Product::query()
            ->search($q)
            ->where('status', 'active');

        // return top products and return id, name, unit, capital
        $products = $productsQuery
            ->limit($limit)
            ->get(['id','name','unit','capital']);


        // Always attach inventory from the terminal's branch context.
        if ($products->isNotEmpty()) {
            $prodIds = $products->pluck('id')->all();
            $inventories = BranchInventory::where('branch_id', $branchId)
                ->whereIn('product_id', $prodIds)
                ->get()
                ->keyBy('product_id');

            $products = $products->map(function ($p) use ($inventories) {
                $p->available_quantity = optional($inventories->get($p->id))->quantity ?? 0;
                return $p;
            });
        }

        return response()->json($products);
    }

    public function browse(Request $request)
    {
        $q = $request->query('q');
        $branchId = $this->resolveTerminalBranchId($request);
        $perPage = min(max((int) $request->query('per_page', 25), 1), 100);

        $products = Product::query()
            ->search($q)
            ->where('status', 'active')
            ->paginate($perPage, ['id','name','unit','capital']);

        if ($products->isNotEmpty()) {
            $productIds = $products->getCollection()->pluck('id')->all();

            $availableByProduct = BranchInventory::query()
                ->where('branch_id', $branchId)
                ->whereIn('product_id', $productIds)
                ->get()
                ->keyBy('product_id');

            $products->setCollection(
                $products->getCollection()->map(function ($product) use ($availableByProduct) {
                    $product->available_quantity = (float) (optional($availableByProduct->get($product->id))->quantity ?? 0);

                    return $product;
                })
            );
        }

        return response()->json($products);
    }
}
