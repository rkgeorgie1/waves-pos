<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashRegister extends Model
{
    use BelongsToTenant;

    protected $guarded = [];

    public function shifts(): HasMany
    {
        return $this->hasMany(CashShift::class);
    }
}
