<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\WhatsApp;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlatformController extends Controller
{
    public function dashboard(): View
    {
        $subscriptions = Subscription::withoutGlobalScopes()->get();

        return view('platform.dashboard', [
            'tenantCount' => Tenant::count(),
            'activeSubscriptionCount' => $subscriptions->filter(fn (Subscription $subscription): bool => $subscription->isCurrentlyActive())->count(),
            'planCount' => Plan::where('is_active', true)->count(),
            'recentTenants' => Tenant::latest()->limit(5)->get(),
        ]);
    }

    public function settings(): View
    {
        return view('platform.settings', [
            'whatsappNumber' => WhatsApp::setting('whatsapp_number') ?? '',
            'whatsappCountryCode' => WhatsApp::countryCode(),
        ]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
            'whatsapp_country_code' => ['nullable', 'string', 'max:5', 'regex:/^\d{1,4}$/'],
        ]);

        WhatsApp::saveSettings($data);

        return back()->with('success', 'WhatsApp payment settings updated.');
    }

    public function tenants(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $tenants = Tenant::query()
            ->when($search !== '', function ($tenantsQuery) use ($search): void {
                $tenantsQuery->whereHas('users', fn ($userQuery) => $userQuery->where('email', 'like', '%'.$search.'%'));
            })
            ->orderBy('name')
            ->get();

        $subscriptions = Subscription::withoutGlobalScopes()->get()->keyBy('tenant_id');

        return view('platform.tenants', [
            'tenants' => $tenants,
            'subscriptions' => $subscriptions,
            'plans' => Plan::where('is_active', true)->orderBy('name')->get(),
            'userCounts' => User::query()->selectRaw('tenant_id, COUNT(*) as total')->groupBy('tenant_id')->pluck('total', 'tenant_id'),
            'productCounts' => Product::withoutGlobalScopes()->selectRaw('tenant_id, COUNT(*) as total')->groupBy('tenant_id')->pluck('total', 'tenant_id'),
            'search' => $search,
        ]);
    }

    public function updateTenantStatus(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['active', 'suspended'])],
        ]);

        $tenant->update(['status' => $data['status']]);

        return back()->with('success', "Organization {$tenant->name} is now {$data['status']}.");
    }

    public function updateTenantPlan(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'plan_id' => ['required', Rule::exists('plans', 'id')],
            'billing_interval' => ['required', Rule::in(['monthly', 'yearly'])],
        ]);

        $plan = Plan::findOrFail($data['plan_id']);
        $endsAt = $data['billing_interval'] === 'yearly' ? now()->addYear() : now()->addMonth();

        Subscription::withoutGlobalScopes()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'plan_id' => $plan->id,
                'status' => 'active',
                'billing_interval' => $data['billing_interval'],
                'current_period_start' => now(),
                'current_period_end' => $endsAt,
                'canceled_at' => null,
            ]
        );

        $tenant->update([
            'plan' => $plan->slug,
            'subscription_status' => 'active',
            'trial_ends_at' => null,
        ]);

        return back()->with('success', "{$tenant->name} is now on the {$plan->name} plan.");
    }

    public function plans(): View
    {
        return view('platform.plans', [
            'plans' => Plan::orderBy('monthly_price')->get(),
        ]);
    }

    public function updatePlan(Request $request, Plan $plan): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'yearly_price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'max_users' => ['nullable', 'integer', 'min:1'],
            'max_products' => ['nullable', 'integer', 'min:1'],
            'max_warehouses' => ['nullable', 'integer', 'min:1'],
            'max_sales_per_month' => ['nullable', 'integer', 'min:1'],
            'stripe_price_id' => ['nullable', 'string', 'max:255'],
            'stripe_yearly_price_id' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $plan->update([
            'name' => $data['name'],
            'description' => $data['description'] ?: null,
            'monthly_price' => $data['monthly_price'],
            'yearly_price' => $data['yearly_price'],
            'currency' => strtoupper($data['currency']),
            'max_users' => $data['max_users'],
            'max_products' => $data['max_products'],
            'max_warehouses' => $data['max_warehouses'],
            'max_sales_per_month' => $data['max_sales_per_month'],
            'stripe_price_id' => $data['stripe_price_id'] ?: null,
            'stripe_yearly_price_id' => $data['stripe_yearly_price_id'] ?: null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', "Plan {$plan->name} updated.");
    }

    public function storePlan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:plans,slug', 'regex:/^[a-z0-9-]+$/'],
            'description' => ['nullable', 'string', 'max:1000'],
            'monthly_price' => ['required', 'numeric', 'min:0'],
            'yearly_price' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'max_users' => ['nullable', 'integer', 'min:1'],
            'max_products' => ['nullable', 'integer', 'min:1'],
            'max_warehouses' => ['nullable', 'integer', 'min:1'],
            'max_sales_per_month' => ['nullable', 'integer', 'min:1'],
            'stripe_price_id' => ['nullable', 'string', 'max:255'],
            'stripe_yearly_price_id' => ['nullable', 'string', 'max:255'],
        ]);

        $data['currency'] = strtoupper($data['currency']);
        $data['is_active'] = true;

        Plan::create($data);

        return back()->with('success', "Plan {$data['name']} created.");
    }
}
