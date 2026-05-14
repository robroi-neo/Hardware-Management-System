<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of suppliers with sorting and search
     */
    public function index(Request $request)
    {
        $sortBy = $request->query('sort_by', 'created_at');
        $sortDir = $request->query('sort_dir', 'desc');
        $search = $request->query('search', '');
        $status = $request->query('status', '');
        $statuses = collect([
            ['value' => 'active', 'label' => 'Active'],
            ['value' => 'inactive', 'label' => 'Inactive'],
        ]);

        // Whitelist allowed columns for sorting
        $allowedColumns = ['id', 'supplier_name', 'contact_person', 'contact_number', 'status', 'created_at'];
        if (!in_array($sortBy, $allowedColumns, true)) {
            $sortBy = 'created_at';
        }

        if (!in_array($sortDir, ['asc', 'desc'], true)) {
            $sortDir = 'desc';
        }

        // Build query
        $query = Supplier::query();

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('supplier_name', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhere('contact_email', 'like', "%{$search}%");
            });
        }

        // Apply status filter
        if ($status) {
            $query->where('status', $status);
        }

        // Apply sorting and pagination
        $suppliers = $query->orderBy($sortBy, $sortDir)->paginate(15);

        return view('modules.suppliers.suppliers', [
            'suppliers' => $suppliers,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'search' => $search,
            'status' => $status,
            'statuses' => $statuses,
        ]);
    }

    /**
     * Store a newly created supplier in the database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:255|unique:suppliers,supplier_name',
            'contact_person' => 'required|string|max:255',
            'company_address' => 'required|string|max:500',
            'contact_number' => 'required|regex:/^09\d{9}$/',
            'contact_email' => 'required|email|max:255',
            'status' => 'required|in:active,inactive',
        ]);
        try {
            $supplier = Supplier::create($validated);

            if ($request->expectsJson()) {
                session()->flash('success', 'Supplier successfully added!');

                return response()->json([
                    'message' => 'Supplier successfully added!',
                    'supplier' => $supplier,
                ], 201);
            }

            return back()
                ->with('success', 'Supplier successfully added!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                session()->flash('error', 'Failed to add supplier. Please try again.');

                return response()->json([
                    'message' => 'Failed to add supplier. Please try again.',
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'Failed to add supplier. Please try again.');
        }
    }

    /**
     * Update the specified supplier in the database
     */
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:255|unique:suppliers,supplier_name,' . $supplier->id,
            'contact_person' => 'nullable|string|max:255',
            'company_address' => 'nullable|string|max:500',
            'contact_number' => 'required|regex:/^09\d{9}$/',
            'contact_email' => 'nullable|email|max:255',
            'status' => 'required|in:active,inactive',
        ]);

        try {
            $supplier->update($validated);

            if ($request->expectsJson()) {
                session()->flash('success', 'Supplier details successfully updated!');

                return response()->json([
                    'message' => 'Supplier details successfully updated!',
                    'supplier' => $supplier->fresh(),
                ], 200);
            }

            return back()
                ->with('success', 'Supplier details successfully updated!');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                session()->flash('error', 'Failed to update supplier. Please try again.');

                return response()->json([
                    'message' => 'Failed to update supplier. Please try again.',
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'Failed to update supplier. Please try again.');
        }
    }

    /**
     * Remove the specified supplier from the database
     */
    public function destroy(Supplier $supplier)
    {
        try {
            // Check if supplier has associated purchases
            if ($supplier->purchases()->exists()) {
                return back()->with('error', 'Cannot delete supplier with existing purchases. Deactivate instead.');
            }

            $supplier->delete();

            return redirect()->route('suppliers.index')
                ->with('success', 'Supplier deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete supplier: ' . $e->getMessage());
        }
    }

    public function deactivate(Request $request, Supplier $supplier)
    {
        try {
            $supplier->update([
                'status' => 'inactive',
            ]);

            if ($request->expectsJson()) {
                session()->flash('success', 'Supplier successfully deactivated!');

                return response()->json([
                    'message' => 'Supplier successfully deactivated!',
                    'supplier' => $supplier->fresh(),
                ], 200);
            }

            return back()
                ->with('success', 'Supplier successfully deactivated!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                session()->flash('error', 'Failed to deactivate supplier. Please try again.');

                return response()->json([
                    'message' => 'Failed to deactivate supplier. Please try again.',
                ], 500);
            }

            return back()->with('error', 'Failed to deactivate supplier. Please try again.');
        }
    }

    public function activate(Request $request, Supplier $supplier)
    {
        try {
            $supplier->update([
                'status' => 'active',
            ]);

            if ($request->expectsJson()) {
                session()->flash('success', 'Supplier successfully activated!');

                return response()->json([
                    'message' => 'Supplier successfully activated!',
                    'supplier' => $supplier->fresh(),
                ], 200);
            }

            return back()
                ->with('success', 'Supplier successfully activated!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                session()->flash('error', 'Failed to activate supplier. Please try again.');

                return response()->json([
                    'message' => 'Failed to activate supplier. Please try again.',
                ], 500);
            }

            return back()->with('error', 'Failed to activate supplier. Please try again.');
        }
    }

    public function search(Request $request)
    {
        $query = trim((string) $request->query('q', ''));
        $limit = (int) $request->query('limit', 8);
        $status = $request->query('status');

        if ($query === '') {
            return response()->json([]);
        }

        $limit = max(1, min($limit, 20));

        $suppliersQuery = Supplier::query()
            ->where(function ($builder) use ($query) {
                $builder->where('supplier_name', 'like', "%{$query}%")
                    ->orWhere('contact_person', 'like', "%{$query}%")
                    ->orWhere('contact_number', 'like', "%{$query}%")
                    ->orWhere('contact_email', 'like', "%{$query}%")
                    ->orWhere('id', $query);
            });

        if (in_array($status, ['active', 'inactive'], true)) {
            $suppliersQuery->where('status', $status);
        }

        $suppliers = $suppliersQuery
            ->orderBy('supplier_name')
            ->limit($limit)
            ->get();

        $payload = $suppliers->map(function (Supplier $supplier) {
            return [
                'id' => $supplier->id,
                'supplier_name' => $supplier->supplier_name,
                'contact_number' => $supplier->contact_number,
                'status' => $supplier->status,
            ];
        });

        return response()->json($payload);
    }
}
