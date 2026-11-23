<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Cashier\Http\Controllers\WebhookController;

class StripeWebhookController extends WebhookController
{
    /**
     * Let Cashier persist its state first, then mirror the fields that drive
     * the application's plan limits and access checks.
     */
    public function handleWebhook(Request $request)
    {
        $payload = json_decode($request->getContent(), true) ?: [];
        $response = parent::handleWebhook($request);

        $this->syncLocalSubscription($payload);

        return $response;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function syncLocalSubscription(array $payload): void
    {
        $eventType = (string) ($payload['type'] ?? '');
        $object = $payload['data']['object'] ?? [];

        if (! is_array($object) || ! str_starts_with($eventType, 'customer.subscription.')) {
            return;
        }

        $subscription = Subscription::where('stripe_id', $object['id'] ?? null)->first();

        if ($subscription === null) {
            return;
        }

        $updates = [];

        if (isset($object['status'])) {
            $updates['status'] = (string) $object['status'];
        }

        foreach (['current_period_start', 'current_period_end', 'canceled_at'] as $field) {
            if (isset($object[$field])) {
                $updates[$field] = Carbon::createFromTimestamp((int) $object[$field]);
            }
        }

        $priceId = $object['items']['data'][0]['price']['id'] ?? null;
        $plan = null;

        if (is_string($priceId)) {
            $plan = Plan::where('stripe_price_id', $priceId)
                ->orWhere('stripe_yearly_price_id', $priceId)
                ->first();

            if ($plan !== null) {
                $updates['plan_id'] = $plan->id;
                $updates['billing_interval'] = $plan->stripe_yearly_price_id === $priceId ? 'yearly' : 'monthly';
            }
        }

        if ($updates === []) {
            return;
        }

        $subscription->update($updates);

        $tenant = Tenant::find($subscription->tenant_id);

        if ($tenant !== null) {
            $tenant->update([
                'subscription_status' => $updates['status'] ?? $tenant->subscription_status,
                'plan' => $plan?->slug ?? $tenant->plan,
            ]);
        }
    }
}
