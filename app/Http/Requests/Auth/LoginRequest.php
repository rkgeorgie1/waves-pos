<?php

namespace App\Http\Requests\Auth;

use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\TenantContext;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $email = $this->string('email')->lower()->toString();
        $user = User::whereRaw('lower(email) = ?', [$email])->first();

        // Establish tenant context before Auth::attempt() and audit logging.
        if ($user !== null) {
            $tenant = Tenant::find($user->tenant_id);

            if ($tenant !== null) {
                app(TenantContext::class)->set($tenant);
            }
        }

        $this->ensureUserIsNotLocked($user);
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());
            $user?->recordFailedLogin();
            AuditLog::record('auth.login_failed', $user, ['email' => $email]);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        if (! Auth::user()?->is_active) {
            AuditLog::record('auth.login_blocked', Auth::user(), ['email' => $this->string('email')->lower()->toString()]);
            Auth::logout();
            $this->session()->invalidate();
            $this->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'This account is inactive. Please contact your administrator.',
            ]);
        }

        Auth::user()?->clearLoginFailures();
        AuditLog::record('auth.login', Auth::user());
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the account is not temporarily locked after repeated failures.
     *
     * @throws ValidationException
     */
    private function ensureUserIsNotLocked(?User $user): void
    {
        if (! $user?->isLocked()) {
            return;
        }

        AuditLog::record('auth.login_locked', $user);

        throw ValidationException::withMessages([
            'email' => 'This account is temporarily locked. Try again in '.$user->lockoutRemainingMinutes().' minutes.',
        ]);
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
