<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\PlanLimits;
use App\Models\TenantContext;
use App\WhatsApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function index(PlanLimits $limits): View
    {
        $metrics = ['users', 'products', 'warehouses', 'sales_this_month'];

        $usage = collect($metrics)->mapWithKeys(fn (string $metric): array => [
            $metric => [
                'used' => $limits->usageFor($metric),
                'limit' => $limits->limitFor($metric),
            ],
        ]);

        return view('billing.index', [
            'subscription' => $limits->subscription(),
            'plan' => $limits->plan(),
            'usage' => $usage,
            'plans' => Plan::where('is_active', true)->orderBy('monthly_price')->get(),
            'whatsappConfigured' => WhatsApp::configured(),
        ]);
    }

    public function pay(Plan $plan, Request $request, TenantContext $tenantContext): RedirectResponse
    {
        $data = $request->validate([
            'interval' => ['required', Rule::in(['monthly', 'yearly'])],
        ]);

        if (! $plan->is_active) {
            abort(404);
        }

        if (! WhatsApp::configured()) {
            return back()->withErrors(['plan' => 'WhatsApp payments are not configured yet. Please contact the administrator.']);
        }

        $tenant = $tenantContext->current();

        return redirect()->away(WhatsApp::chatUrl(
            WhatsApp::paymentMessage($plan, $data['interval'], $tenant?->name)
        ));
    }

    public function portal(): RedirectResponse
    {
        return redirect()->route('billing.index');
    }

    public function cancel(TenantContext $tenantContext): RedirectResponse
    {
        $tenant = $tenantContext->current();
        $subscription = $tenant?->currentSubscription();

        if ($subscription === null) {
            return back()->withErrors(['portal' => 'There is no subscription to cancel.']);
        }

        $subscription->update([
            'status' => 'cancelled',
            'stripe_status' => 'cancelled',
            'ends_at' => now(),
            'canceled_at' => now(),
        ]);

        $tenant->update(['subscription_status' => 'cancelled']);

        return back()->with('success', 'Your subscription has been cancelled.');
    }

    public function invoices(): View
    {
        return view('billing.invoices', ['invoices' => collect()]);
    }
}
