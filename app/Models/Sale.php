<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use BelongsToTenant;

    protected $guarded = [];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'payable_id')->where('payable_type', self::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(SalesReturn::class);
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        if ($this->relationLoaded('payments')) {
            $count = $this->payments->count();
            if ($count > 1) {
                return 'Multiple Pay';
            }
            if ($count === 1) {
                return ucwords(str_replace('_', ' ', $this->payments->first()->method));
            }
        }

        if (! empty($this->payment_method)) {
            return ucwords(str_replace('_', ' ', $this->payment_method));
        }

        return '-';
    }
}
