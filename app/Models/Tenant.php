<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

#[Fillable(['name', 'slug', 'domain', 'status', 'plan', 'subscription_status', 'trial_ends_at'])]
class Tenant extends Model
{
    use Billable, Notifiable;

    protected function casts(): array
    {
        return [
            'trial_ends_at' => 'datetime',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function currentSubscription(): ?Subscription
    {
        return $this->subscriptions()->withoutGlobalScopes()->latest()->first();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
