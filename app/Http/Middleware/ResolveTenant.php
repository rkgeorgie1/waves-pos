<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Models\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            $tenant = Tenant::withoutGlobalScopes()->find($user->tenant_id);

            if ($tenant === null || ! $tenant->isActive()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                abort(403, 'Your organization is not active. Please contact support.');
            }

            app(TenantContext::class)->set($tenant);
        }

        return $next($request);
    }
}
