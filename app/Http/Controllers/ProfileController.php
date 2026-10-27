<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        if (\App\Support\Demo::isDemoUser($request->user())) {
            return Redirect::route('profile.edit')->with('error', 'Profile changes are turned off for demo accounts.');
        }

        $request->user()->fill($request->validated());

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }
}
