<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Support\Demo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Demo entry: pick a role (step 1), pick a sample branch (step 2), and get
 * signed in as that role's demo user. Also used to switch role or branch
 * from inside the demo.
 */
class DemoController extends Controller
{
    /** Role cards: name, one line, what to try, and the modules it sees. */
    public const ROLE_CARDS = [
        'admin' => [
            'name' => 'Admin',
            'blurb' => 'Owner-level access to everything, including users, roles and audit logs.',
            'try' => 'add a cashier, then check the activity log.',
            'modules' => ['Dashboard', 'POS', 'Inventory', 'Purchasing', 'Suppliers', 'Users', 'Audit logs'],
        ],
        'manager' => [
            'name' => 'Manager',
            'blurb' => 'Runs a branch day to day: stock, buying from suppliers, refunds.',
            'try' => 'receive a supplier invoice and watch stock go up.',
            'modules' => ['Dashboard', 'POS', 'Inventory', 'Purchasing', 'Suppliers', 'Refunds'],
        ],
        'cashier' => [
            'name' => 'Cashier',
            'blurb' => 'The counter view, built for speed.',
            'try' => 'search for a product and ring up a sale.',
            'modules' => ['New sale'],
        ],
    ];

    /** Short description of each branch's catalog, by position. */
    private const BRANCH_LINES = [
        0 => 'Tools, fasteners, motor parts',
        1 => 'Tiles, grout, flooring',
    ];

    public function role(Request $request): View
    {
        $selected = $request->query('role', session('demo.role', 'manager'));

        return view('demo.role', [
            'roles' => self::ROLE_CARDS,
            'selected' => array_key_exists($selected, self::ROLE_CARDS) ? $selected : 'manager',
        ]);
    }

    public function branch(Request $request): View|RedirectResponse
    {
        $role = $request->query('role');

        if (! array_key_exists($role, self::ROLE_CARDS)) {
            return redirect()->route('demo');
        }

        $counts = DB::table('branch_inventory')->selectRaw('branch_id, count(*) as products')
            ->groupBy('branch_id')->pluck('products', 'branch_id');

        $branches = Branch::orderBy('id')->get()->values()->map(fn (Branch $branch, int $i) => [
            'id' => $branch->id,
            'name' => $branch->name,
            'line' => self::BRANCH_LINES[$i] ?? $branch->address,
            'products' => (int) ($counts[$branch->id] ?? 0),
            'icon' => $i === 1 ? 'tiles' : 'hardware',
        ]);

        return view('demo.branch', [
            'role' => $role,
            'roleName' => self::ROLE_CARDS[$role]['name'],
            'branches' => $branches,
        ]);
    }

    public function start(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(Demo::ROLES)],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
        ]);

        $user = Demo::user($data['role']);

        if (! $user || $user->status !== 'active') {
            // Can happen for a moment while the demo data is being reset.
            return redirect()->route('demo')->with('error', 'The demo is resetting. Try again in a few seconds.');
        }

        $branch = Branch::findOrFail($data['branch_id']);

        Auth::login($user);
        $request->session()->regenerate();

        // Start clean: carts from a previous role or branch would be confusing.
        $request->session()->forget(['pos_cart', 'purchasing_cart', 'purchasing_supplier_id']);
        $request->session()->put('branch', $branch->only('id', 'name', 'address'));
        $request->session()->put('demo', ['role' => $data['role'], 'started_at' => now()->toIso8601String()]);

        return redirect()->route(Demo::homeRoute($user));
    }
}
