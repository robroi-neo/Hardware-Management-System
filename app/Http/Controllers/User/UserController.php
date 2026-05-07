<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'role' => 'required',
            'pin' => 'required|confirmed',
            'pin_confirmation' => 'required',
        ]);

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
