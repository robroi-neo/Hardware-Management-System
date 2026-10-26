<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Services\Pos\RefundService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class RefundController extends Controller
{
    public function store(Request $request, Sale $sale, RefundService $service)
    {
        $validated = $request->validate([
            'reason_id' => ['required', 'integer', 'exists:refund_reasons,id'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $service->refund($sale, (int) $validated['reason_id'], $validated['note'] ?? null, auth()->id());

            return response()->json([
                'success' => true,
                'message' => 'Transaction refunded successfully. Inventory has been restored.',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            Log::error('Refund error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error processing refund: ' . $e->getMessage(),
            ], 500);
        }
    }
}

