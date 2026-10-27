<?php

namespace App\Http\Middleware;

use App\Http\Controllers\DemoController;
use App\Support\Demo;
use App\Support\DemoState;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shares the demo banner's data with every view, and tells visitors whose
 * demo account vanished (the data was reset) what happened.
 */
class DemoContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $demo = $request->session()->get('demo');

        if (! $user && $demo) {
            // They were in the demo, but their account is gone: the data was reset.
            $request->session()->forget(['demo', 'branch', 'pos_cart', 'purchasing_cart', 'purchasing_supplier_id']);
            $request->session()->flash('error', 'The demo data was just reset. Pick a role to start again.');
        }

        if ($user && $demo && isset(DemoController::ROLE_CARDS[$demo['role'] ?? ''])) {
            $role = $demo['role'];

            View::share('demo', [
                'role' => $role,
                'roleName' => DemoController::ROLE_CARDS[$role]['name'],
                'branch' => $request->session()->get('branch.name'),
                'nextResetAt' => DemoState::nextResetAt()->toIso8601String(),
                'tasks' => array_map(fn ($task) => [
                    'label' => $task[0],
                    'url' => $task[1] === 'demo.branch' ? route('demo.branch', ['role' => $role]) : route($task[1]),
                ], Demo::tasks($role)),
            ]);
        }

        return $next($request);
    }
}
