<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SellingPriceGroup extends Model
{
    use BelongsToTenant;

    protected $guarded = [];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_selling_prices')->withPivot('price')->withTimestamps();
    }
}
