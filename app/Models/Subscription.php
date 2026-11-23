<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Cashier\Subscription as CashierSubscription;

/**
 * Tenant subscription. Extends Cashier's model so Stripe remains the source
 * of truth while the local plan_id/status fields drive plan-limit checks.
 *
 * Deliberately not tenant-scoped globally: Cashier webhooks must be able to
 * resolve a subscription by Stripe customer without a request tenant context.
 */
class Subscription extends CashierSubscription
{
    protected static function booted(): void
    {
        // Cashier's webhooks create subscription rows before the local plan
        // mapping is applied, so always give them a safe default plan.
        static::creating(function (self $subscription): void {
            if ($subscription->plan_id === null) {
                $subscription->plan_id = Plan::where('slug', 'free')->value('id');
            }
        });
    }

    protected function casts(): array
    {
        return array_merge(parent::casts(), [
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'canceled_at' => 'datetime',
        ]);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function isCurrentlyActive(): bool
    {
        if (! in_array($this->status, ['trialing', 'active', 'past_due'], true)) {
            return false;
        }

        if ($this->status === 'trialing' && $this->trial_ends_at?->isPast()) {
            return false;
        }

        if ($this->status === 'active' && $this->current_period_end?->isPast()) {
            return false;
        }

        return true;
    }
}
