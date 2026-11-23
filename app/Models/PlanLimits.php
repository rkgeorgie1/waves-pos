<?php

namespace App\Models;

final class PlanLimits
{
    public function __construct(private readonly TenantContext $tenantContext) {}

    public function subscription(): ?Subscription
    {
        $tenantId = $this->tenantContext->id();

        if ($tenantId === null) {
            return null;
        }

        $subscriptions = Subscription::where('tenant_id', $tenantId)->get();

        // A Stripe-backed row supersedes a legacy row created before checkout.
        return $subscriptions->first(fn (Subscription $subscription): bool => $subscription->stripe_id !== null)
            ?? $subscriptions->first();
    }

    public function plan(): ?Plan
    {
        return $this->subscription()?->plan;
    }

    public function limitFor(string $metric): ?int
    {
        $column = match ($metric) {
            'users' => 'max_users',
            'products' => 'max_products',
            'warehouses' => 'max_warehouses',
            'sales_this_month' => 'max_sales_per_month',
            default => null,
        };

        if ($column === null) {
            return null;
        }

        $limit = $this->plan()?->{$column};

        return $limit === null ? null : (int) $limit;
    }

    public function usageFor(string $metric): int
    {
        return match ($metric) {
            'users' => User::where('tenant_id', $this->tenantContext->id())->count(),
            'products' => Product::count(),
            'warehouses' => Warehouse::count(),
            'sales_this_month' => Sale::whereBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ])->count(),
            default => 0,
        };
    }

    public function allows(string $metric, int $additional = 1): bool
    {
        $limit = $this->limitFor($metric);

        if ($limit === null) {
            return true;
        }

        return $this->usageFor($metric) + $additional <= $limit;
    }
}
