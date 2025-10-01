<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $validRoles = ['admin', 'guru'];

        if (!app()->isProduction()) {
            $validRoles[] = 'superadmin';
        }

        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'role' => ['required', 'string', Rule::in($validRoles)],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // 1. Coba otentikasi dasar.
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // 2. Jika otentikasi berhasil, periksa role.
        $user = Auth::user();
        $selectedRole = $this->role;

        // ASUMSI: Model User memiliki kolom 'role'
        if ($user->role !== $selectedRole) {

            // LOGIKA PEMBERSIHAN EKSPlisit: Logout dan invalidasi sesi saat role salah
            Auth::logout();
            $this->session()->invalidate();
            $this->session()->regenerateToken();

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'role' => 'Peran yang Anda pilih tidak cocok dengan peran akun ini.',
            ]);
        }

        // 3. Jika role cocok.
        RateLimiter::clear($this->throttleKey());
    }

    // ... (ensureIsNotRateLimited() dan throttleKey() tetap sama)
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => __('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
