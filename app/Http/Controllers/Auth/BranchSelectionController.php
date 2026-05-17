<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchSelectionController extends Controller
{
    /**
     * Display terminal picker before login.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('branch')) {
            return redirect()->route('login');
        }

        $branches = Branch::all();

        return view('auth.select-branch', compact('branches'));
    }

    /**
     * Persist selected terminal in session.
     */
    public function store(Request $request): RedirectResponse
    {

        $data = $request->validate([
            'branch_id' => 'required|integer|exists:branches,id',
        ]);

        $branch = Branch::find($data['branch_id']);

        $request->session()->put('branch', [
            'id' => $branch->id,
            'name' => $branch->name,
            'address' => $branch->address,
        ]);

        return redirect()->route('login');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->session()->forget('branch');

        return redirect()->route('branch.select');
    }
}

