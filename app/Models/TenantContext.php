<?php

namespace App\Models;

/**
 * Holds the tenant resolved for the current request/job.
 */
final class TenantContext
{
    private ?Tenant $tenant = null;

    public function set(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function current(): ?Tenant
    {
        return $this->tenant;
    }

    public function id(): ?int
    {
        if ($this->tenant !== null) {
            return $this->tenant->id;
        }

        // Route bindings are substituted before the tenant middleware runs, so
        // fall back to the authenticated user's tenant to keep scoped queries working.
        return auth()->user()?->tenant_id;
    }

    public function hasCurrent(): bool
    {
        return $this->tenant !== null;
    }

    public function forget(): void
    {
        $this->tenant = null;
    }
}
