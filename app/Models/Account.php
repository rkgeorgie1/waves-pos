<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use BelongsToTenant;

    protected $guarded = [];
}
