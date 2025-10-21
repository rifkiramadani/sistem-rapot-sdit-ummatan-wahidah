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

        // AMBIL ROLE DARI REQUEST (FORM LOGIN)
        // (e.g., 'guru')
        $roleFromRequest = $this->input('role');

        // AMBIL ROLE ASLI PENGGUNA DARI DATABASE
        // (e.g., 'teacher')
        $actualRoleInDb = $user->role->name; // <-- INI PERBAIKANNYA

        // 3. LOGIKA KONVERSI: Mapping nilai role dari Frontend (request)
        //    ke nilai standar di database (RoleEnum)
        $expectedRoleInDb = match ($roleFromRequest) {
            'admin'      => RoleEnum::ADMIN->value,      // 'admin' -> 'admin'
            'guru'       => RoleEnum::TEACHER->value,    // 'guru' -> 'teacher'
            'superadmin' => RoleEnum::SUPERADMIN->value, // 'superadmin' -> 'superadmin'
            // Tambahkan case lain jika ada, e.g., 'kepsek' => RoleEnum::PRINCIPAL->value
            default      => null,
        };

        // 4. Bandingkan string role asli di DB dengan string role yang diharapkan dari form
        //    Contoh: $actualRoleInDb ('teacher') !== $expectedRoleInDb ('teacher') -> false (cocok)
        //    Contoh: $actualRoleInDb ('admin')   !== $expectedRoleInDb ('teacher') -> true (tidak cocok)
        if ($actualRoleInDb !== $expectedRoleInDb) {

            // Logout, invalidasi sesi, dan regenerate token saat role salah
            Auth::logout();
            $this->session()->invalidate();
            $this->session()->regenerateToken();

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                // Kembalikan error ke field 'role'
                'role' => 'Peran yang Anda pilih tidak cocok dengan akun ini.',
            ]);
        }

        // 5. Role cocok
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
