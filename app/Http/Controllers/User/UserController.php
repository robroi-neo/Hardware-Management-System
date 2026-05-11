<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $sortBy = $request->query('sort_by', 'name');
        $sortDir = $request->query('sort_dir', 'asc');
        $branches = Branch::all();

        // apply sorting before get.
        $users = User::with('branch')
            ->orderBy(
                Branch::select('name')
                    ->whereColumn('branches.id', 'users.branch_id'),
                $sortDir
            )
            ->paginate(10)
            ->withQueryString();

        return view('modules.users.users', compact('users', 'sortBy', 'sortDir', 'branches'));
    }
    public function create()
    {
        $branches = Branch::all();
        $roles = Role::all();

        return view('modules.users.new-user', compact('roles','branches'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'role'      => 'required|string|exists:roles,name',
            'branch_id' => 'nullable|exists:branches,id',
            'status'    => 'required|in:active,inactive',
        ]);

        try {
            $user->update([
                'name'      => $validated['name'],
                'phone'     => $validated['phone'],
                'branch_id' => $validated['branch_id'],
                'status'    => $validated['status'],
            ]);

            $user->syncRoles([$validated['role']]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'User updated successfully',
                    'user'    => $user->fresh()->load('roles', 'branch'),
                ], 200);
            }

            return redirect()->route('users.index')
                ->with('success', 'User updated successfully');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to update user: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'Failed to update user: ' . $e->getMessage());
        }
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'role' => 'required',
            'pin' => 'required|string|min:4|max:10|confirmed',
            'pin_confirmation' => 'required',
            'branch_id' => 'required',
        ]);
        // attach branch from choices to user
        // attach the user that created the user.
        try {
            // Remove fields that should NOT go into users table
            unset($validated['pin_confirmation']);

            $role = $validated['role'];
            unset($validated['role']);

            $validated['created_by'] = auth()->id();

            $user = User::create($validated);

            // assign Spatie role (correct place for role)
            $user->assignRole($role);

            return back()->with('success', 'User created successfully');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Failed to create user: ' . $e->getMessage());
        }
    }
}
