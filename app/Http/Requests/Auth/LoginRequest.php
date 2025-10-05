<?php

namespace App\Http\Requests\Auth;

use App\Enums\RoleEnum;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log; // <-- IMPORT FACADE LOG DITAMBAHKAN
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
        // Mendefinisikan role yang valid berdasarkan nilai yang dikirim dari frontend (HTML/TS)
        $validRoles = [
            'admin',
            'guru', // Nilai yang dikirim oleh TabsTrigger di frontend
        ];

        // Wrap the "Super Admin" option in a conditional statement (Backend Validation)
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


        // 1. Otentikasi Email & Password
        if (!Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // 2. Jika otentikasi berhasil, periksa role.
        $user = Auth::user();
        $selectedRole = $this->role;

        // LOGIKA KONVERSI KRITIS: Mapping nilai role dari Frontend ke nilai RoleEnum (Database)
        $expectedRoleInDb = match ($selectedRole) {
            'admin' => RoleEnum::ADMIN->value,      // 'admin' -> 'admin'
            'guru' => RoleEnum::TEACHER->value,     // 'guru' -> 'teacher'
            'superadmin' => RoleEnum::SUPERADMIN->value, // 'superadmin' -> 'superadmin'
            default => null,
        };

        if ($user->role !== $expectedRoleInDb) {

            // Logout, invalidasi sesi, dan regenerate token saat role salah
            Auth::logout();
            $this->session()->invalidate();
            $this->session()->regenerateToken();

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'role' => 'Peran yang Anda pilih tidak cocok dengan akun ini.',
            ]);
        }

        // 3. Role cocok
        RateLimiter::clear($this->throttleKey());
    }

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

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')) . '|' . $this->ip());
    }
}
