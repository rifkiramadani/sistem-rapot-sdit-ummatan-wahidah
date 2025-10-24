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
            'kepsek', // TAMBAHAN: Untuk principal
        ];

        // Wrap the "Super Admin" option in a conditional statement (Backend Validation)
        if (!app()->isProduction()) {
            $validRoles[] = 'superadmin';
        }

        $rules = [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'role' => ['required', 'string', Rule::in($validRoles)],
        ];

        // Add school_academic_year_id validation for teacher and principal roles
        if (in_array($this->input('role'), ['guru', 'kepsek'])) {
            $rules['school_academic_year_id'] = ['required', 'ulid', 'exists:school_academic_years,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'school_academic_year_id.required' => 'Tahun ajaran harus dipilih untuk login sebagai guru atau kepala sekolah.',
            'school_academic_year_id.exists' => 'Tahun ajaran yang dipilih tidak valid.',
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

        // PERBAIKAN: Tambahkan pengecekan jika user tidak memiliki role
        if (!$user->role) {
            Auth::logout();
            $this->session()->invalidate();
            $this->session()->regenerateToken();

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'role' => 'Akun Anda tidak memiliki peran yang valid. Silakan hubungi administrator.',
            ]);
        }

        // AMBIL ROLE DARI REQUEST (FORM LOGIN)
        // (e.g., 'guru' atau 'kepsek')
        $roleFromRequest = $this->input('role');

        // AMBIL ROLE ASLI PENGGUNA DARI DATABASE
        // (e.g., 'teacher' atau 'principal')
        $actualRoleInDb = $user->role->name; // Sekarang aman karena sudah dicek null

        // 3. LOGIKA KONVERSI: Mapping nilai role dari Frontend (request)
        //    ke nilai standar di database (RoleEnum)
        $expectedRoleInDb = match ($roleFromRequest) {
            'admin'      => RoleEnum::ADMIN->value,      // 'admin' -> 'admin'
            'guru'       => RoleEnum::TEACHER->value,    // 'guru' -> 'teacher'
            'kepsek'     => RoleEnum::PRINCIPAL->value,  // TAMBAHAN: 'kepsek' -> 'principal'
            'superadmin' => RoleEnum::SUPERADMIN->value, // 'superadmin' -> 'superadmin'
            default      => null,
        };

        // 4. Bandingkan string role asli di DB dengan string role yang diharapkan dari form
        if ($actualRoleInDb !== $expectedRoleInDb) {
            // Logout, invalidasi sesi, dan regenerate token saat role salah
            Auth::logout();
            $this->session()->invalidate();
            $this->session()->regenerateToken();

            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
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
