<?php
namespace App\Http\Controllers\Inventory;
use App\Http\Controllers\Controller;
use App\Services\Inventory\BranchContextService;
use App\Services\Inventory\ProductAvailabilityService;
use App\Services\Inventory\StockMovementService;
use Illuminate\Http\Request;
class StockOutController extends Controller
{
    public function __construct(
        private readonly BranchContextService $branchContextService,
        private readonly ProductAvailabilityService $productAvailabilityService,
        private readonly StockMovementService $stockMovementService,
    ) {
    }
    /**
     * Show the manual stock-out form
     */
    public function create(Request $request)
    {
        return view('modules.inventory.manual-stock-out', $this->branchContextService->formData($request));
    }
    /**
     * Search products for stock-out (API endpoint)
     */
    public function searchProducts(Request $request)
    {
        $q = $request->query('q');
        $limit = min(max((int) $request->query('limit', 20), 1), 50);
        $branchId = (int) $request->query('branch_id', 0) ?: null;
        return response()->json(
            $this->productAvailabilityService->searchActiveProducts($q, $limit, $branchId)
        );
    }
    /**
     * Store stock-out transaction
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'branch_id' => 'required|integer|exists:branches,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'reference_type' => 'nullable|in:sale,transfer,adjustment,other',
            'reference_id' => 'nullable|integer',
            'notes' => 'nullable|string|max:500',
        ]);
        $user = $request->user();
        $isAdmin = $user->hasRole('admin');
        if (!$isAdmin) {
            $sessionBranchId = $this->branchContextService->selectedBranchId($request);
            if (!$sessionBranchId || $sessionBranchId != $data['branch_id']) {
                return response()->json(['message' => 'Unauthorized branch access'], 403);
            }
        }
        $result = $this->stockMovementService->storeStockOut(
            (int) $data['branch_id'],
            $data['items'],
            $user,
            $data['reference_type'] ?? 'other',
            $data['reference_id'] ?? null,
        );
        $status = $result['status'] ?? 200;
        unset($result['status']);
        return response()->json($result, $status);
    }
}
