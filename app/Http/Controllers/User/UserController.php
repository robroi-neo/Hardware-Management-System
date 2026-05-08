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
    public function create()
    {
        $users = User::all();
        $branches = Branch::all();
        $roles = Role::all();

        return view('modules.users.new-user', compact('roles','branches', 'users'));
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
        ]);


        // attach branch from choices to user
        // attach the user that created the user.
        try {
            // Remove fields that should NOT go into users table
            unset($validated['pin_confirmation']);

            $role = $validated['role'];
            unset($validated['role']);

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
