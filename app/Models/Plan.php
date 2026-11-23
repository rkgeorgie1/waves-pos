<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name', 'slug', 'description', 'monthly_price', 'yearly_price', 'currency',
    'max_users', 'max_products', 'max_warehouses', 'max_sales_per_month',
    'features', 'is_active', 'stripe_price_id', 'stripe_yearly_price_id',
])]
class Plan extends Model
{
    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'yearly_price' => 'decimal:2',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
