<?php

namespace App\Http\Middleware;

use App\Models\PlanLimits;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureActiveSubscription
{
    public function __construct(private readonly PlanLimits $planLimits) {}

    public function handle(Request $request, Closure $next): Response
    {
        $subscription = $this->planLimits->subscription();

        if ($subscription === null) {
            return $next($request);
        }

        if (! $subscription->isCurrentlyActive()) {
            abort(402, 'Your subscription is not active. Please update your billing details.');
        }

        return $next($request);
    }
}
