<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class TenantScope implements Scope
{
    public function __construct(private readonly TenantContext $tenantContext) {}

    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = $this->tenantContext->id();

        // Fail closed: an unresolved tenant must never expose another merchant's rows.
        if ($tenantId === null) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $builder->where($model->qualifyColumn('tenant_id'), $tenantId);
    }
}
