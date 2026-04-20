<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PosTerminal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TerminalSelectionController extends Controller
{
    /**
     * Display terminal picker before login.
     */
    public function create(Request $request): View|RedirectResponse
    {
        if ($request->session()->has('pos_terminal')) {
            return redirect()->route('login');
        }

        $terminals = PosTerminal::query()
            ->with('branch:id,name')
            ->orderBy('terminal_id')
            ->get();

        return view('auth.select-terminal', compact('terminals'));
    }

    /**
     * Persist selected terminal in session.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'terminal_id' => ['required', 'integer', 'exists:pos_terminals,id'],
        ]);

        $terminal = PosTerminal::query()
            ->with('branch:id,name')
            ->findOrFail($data['terminal_id']);

        $request->session()->put('pos_terminal', [
            'id' => $terminal->id,
            'terminal_id' => $terminal->terminal_id,
            'terminal_name' => $terminal->terminal_name,
            'branch_id' => $terminal->branch_id,
            'branch_name' => $terminal->branch?->name,
        ]);

        return redirect()->route('login');
    }
}

