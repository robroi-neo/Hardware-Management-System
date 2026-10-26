<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Services\Inventory\BranchContextService;
use App\Services\Inventory\ProductAvailabilityService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private readonly BranchContextService $branchContextService,
        private readonly ProductAvailabilityService $productAvailabilityService,
    ) {
    }

    public function search(Request $request)
    {
        $q = $request->query('q');
        $limit = min(max((int) $request->query('limit', 20), 1), 50);
        $branchId = $this->branchContextService->resolveTerminalBranchId($request);

        return response()->json(
            $this->productAvailabilityService->searchActiveProducts($q, $limit, $branchId)
        );
    }

    public function browse(Request $request)
    {
        $q = $request->query('q');
        $branchId = $this->branchContextService->resolveTerminalBranchId($request);
        $perPage = min(max((int) $request->query('per_page', 25), 1), 100);

        return response()->json(
            $this->productAvailabilityService->browseActiveProducts($q, $perPage, $branchId)
        );
    }
}
