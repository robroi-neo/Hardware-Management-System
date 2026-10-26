<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        // Get query params
        $search = $request->input('search');
        $sort = $request->input('sort', 'id_desc'); // default sorting

        // Build query
        $suppliers = Supplier::query()
            ->when($search, function ($query, $search) {
                $lowerSearch = strtolower($search);
                $query->whereRaw('LOWER(supplier_name) LIKE ?', ["%{$lowerSearch}%"])
                    ->orWhereRaw('LOWER(contact_person) LIKE ?', ["%{$lowerSearch}%"])
                    ->orWhereRaw('LOWER(address) LIKE ?', ["%{$lowerSearch}%"]);
            });

        // Apply sorting
        switch ($sort) {
            case 'id_asc':
                $suppliers->orderBy('id', 'asc');
                break;
            case 'company_asc':
                $suppliers->orderBy('UPPER(supplier_name)', 'asc');
                break;
            case 'company_desc':
                $suppliers->orderByRaw('UPPER(supplier_name) DESC');

                break;
            default: // 'id_desc'
                $suppliers->orderBy('id', 'desc');
                break;
        }

        // Paginate and preserve query params
        $suppliers = $suppliers->paginate(8)->appends([
            'search' => $search,
            'sort' => $sort,
        ]);

        return view('supplier.index', [
            'heading' => 'Supplier Records',
            'suppliers' => $suppliers,
        ]);
    }

    public function store(Request $request)
    {
        
        // Validate
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
        ]);
        // Save to DB later
        Supplier::create($validated);

        return redirect('/supplier')->with('success', 'Supplier created!');
    }
    public function delete(Request $request)
    {
        $id = $request->input('id');
        $supplier = Supplier::find($id);

        if (!$supplier) {
            return response()->json(['error' => 'Supplier not found'], 404);
        }

        $supplier->delete();

        return response()->json(['success' => true]);
    }

    public function patch(Request $request, $id)
    {
        // validate
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'contact_number' => 'required|string|max:20',
        ]);

        // update supplier
        $supplier = Supplier::findOrFail($id);
        $supplier->update([
            "supplier_name" => request('supplier_name'),
            "address" => request('address'),
            "contact_person" => request('contact_person'),
            "contact_number" => \request('contact_number'),
        ]);
        $supplier->save();

        return redirect('/supplier');
    }
}
