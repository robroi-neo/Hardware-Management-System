<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public landing page (demo flow, screen 1).
 */
class LandingController extends Controller
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        // Someone already signed in goes straight back into the app.
        if ($user = $request->user()) {
            return redirect()->route($user->can('dashboard.view') ? 'dashboard' : 'pos');
        }

        return view('landing');
    }
}
