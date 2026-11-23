<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role_id', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    public const MAX_FAILED_LOGIN_ATTEMPTS = 5;

    public const LOGIN_LOCKOUT_MINUTES = 15;

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted(): void
    {
        // Users are intentionally not tenant-scoped globally: the auth guard must
        // resolve the session user before tenant middleware can run.
        static::creating(function (self $user): void {
            $tenantContext = app(TenantContext::class);

            if ($user->tenant_id === null && $tenantContext->hasCurrent()) {
                $user->tenant_id = $tenantContext->id();
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->where('is_read', false)->count();
    }

    public function isLocked(): bool
    {
        return $this->locked_until?->isFuture() ?? false;
    }

    public function lockoutRemainingMinutes(): int
    {
        if (! $this->isLocked()) {
            return 0;
        }

        $seconds = $this->locked_until->getTimestamp() - now()->getTimestamp();

        return (int) ceil(max(0, $seconds) / 60);
    }

    public function recordFailedLogin(): void
    {
        $attempts = $this->failed_login_attempts + 1;
        $values = ['failed_login_attempts' => $attempts];

        if ($attempts >= self::MAX_FAILED_LOGIN_ATTEMPTS) {
            $values['locked_until'] = now()->addMinutes(self::LOGIN_LOCKOUT_MINUTES);
        }

        $this->forceFill($values)->save();
    }

    public function clearLoginFailures(): void
    {
        if ($this->failed_login_attempts === 0 && $this->locked_until === null) {
            return;
        }

        $this->forceFill([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ])->save();
    }

    /**
     * Determine whether the user may perform a permission-guarded action.
     */
    public function hasPermission(string $permission): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->role?->slug === 'super-admin') {
            return true;
        }

        return $this->role?->permissions->contains(
            fn (Permission $grantedPermission): bool => $grantedPermission->slug === $permission
                || $grantedPermission->name === $permission
        ) ?? false;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_platform_admin' => 'boolean',
            'failed_login_attempts' => 'integer',
            'locked_until' => 'datetime',
        ];
    }
}
