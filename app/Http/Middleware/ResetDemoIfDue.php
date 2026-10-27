<?php

namespace App\Http\Middleware;

use App\Services\Demo\DemoReset;
use App\Support\DemoState;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * For hosts without a scheduler: the first request after the reset interval
 * puts the demo data back to the snapshot before it is handled.
 * Off unless DEMO_AUTO_RESET=true, and only when a snapshot exists.
 */
class ResetDemoIfDue
{
    public function __construct(private readonly DemoReset $reset) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (config('demo.auto_reset') && DemoState::isDue() && $this->reset->snapshotExists()) {
            $this->reset->restore();
        }

        return $next($request);
    }
}
