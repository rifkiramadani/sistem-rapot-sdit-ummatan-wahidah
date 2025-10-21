<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\SchoolAcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log; //IMPORT FACADE LOG DITAMBAHKAN
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    public function create(Request $request): Response
    {
        // Get available school academic years for teacher selection
        $schoolAcademicYears = SchoolAcademicYear::with('academicYear')
            ->orderBy('created_at', 'desc')
            ->get(['id', 'academic_year_id']);

        return Inertia::render('auth/login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
            'schoolAcademicYears' => $schoolAcademicYears,
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        //BARIS LOGGING BARU DARI CONTROLLER
        Log::info("Login attempt received {Email: $request->email` | Role Selected: $request->role | Academic Year: " . ($request->school_academic_year_id ?? 'N/A') . "}");

        // Additional validation for teacher role
        if ($request->role === 'guru') {
            $request->validate([
                'school_academic_year_id' => ['required', 'ulid', 'exists:school_academic_years,id'],
            ], [
                'school_academic_year_id.required' => 'Tahun ajaran harus dipilih untuk login sebagai guru.',
                'school_academic_year_id.exists' => 'Tahun ajaran yang dipilih tidak valid.',
            ]);
        }

        // Lakukan otentikasi dan pengecekan role yang kini ada di LoginRequest.php
        $request->authenticate();

        // Additional validation: Check if teacher is associated with the selected academic year
        if ($request->role === 'guru') {
            $user = Auth::user();
            $teacherExists = $user->teacher()
                ->where('school_academic_year_id', $request->school_academic_year_id)
                ->exists();

            if (!$teacherExists) {
                // Store the error message before logging out
                $errorMessage = 'Anda tidak terdaftar sebagai guru pada tahun ajaran yang dipilih. Silakan hubungi administrator.';

                Auth::guard('web')->logout();

                // Start a new session to store the error
                $request->session()->regenerateToken();
                $request->session()->flash('error', $errorMessage);

                return redirect()->route('login')
                    ->onlyInput('email', 'role', 'school_academic_year_id');
            }

            // Store the selected academic year in session for use throughout the application
            $request->session()->put('selected_school_academic_year_id', $request->school_academic_year_id);
        }

        $request->session()->regenerate();

        // Redirect teachers to their selected academic year dashboard
        if ($request->role === 'guru' && $request->school_academic_year_id) {
            return redirect()->intended(route('protected.school-academic-years.dashboard.index', [
                'schoolAcademicYear' => $request->school_academic_year_id
            ], absolute: false));
        }

        return redirect()->intended(route('protected.dashboard.index', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        // Clear selected academic year from session
        $request->session()->forget('selected_school_academic_year_id');

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}