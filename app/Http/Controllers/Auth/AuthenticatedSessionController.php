<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Models\AcademicYear; //Import Model AcademicYear
use Illuminate\Support\Facades\Log; //Import Log facade
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {

        // Ambil data Academic Year dan kirim ke frontend
        $academicYears = AcademicYear::orderBy('start', 'desc')
            ->select('id', 'name')
            ->get();

        return Inertia::render('auth/login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
            'academicYears' => $academicYears, //kirim data academic year ke frontend untuk dropdown
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Ambil nilai academic_year yang sudah divalidasi
        $academicYearId = $request->input('academic_year');

        //Cari model AcademicYear berdasarkan ID
        $academicYear = AcademicYear::find($academicYearId);

        //LOGGING: Catat academic_year yang dipilih ke log file untuk verifikasi
        Log::info('Login Success: Academic Year Selected.', [
            'user_id' => Auth::id(),
            'email' => $request->input('email'),
            'selected_academic_year_id' => $academicYearId,
            //TAMBAHKAN NAMA TAHUN AJARAN DI LOG
            'selected_academic_year_name' => $academicYear ? $academicYear->name : 'N/A',
        ]);

        // (OPSIONAL TAPI BAIK) Simpan ID Tahun Ajaran ke Session untuk akses mendatang
        $request->session()->put('current_academic_year_id', $academicYearId);

        return redirect()->intended(route('protected.dashboard.index', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
