<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcademicYear;
use Illuminate\Http\JsonResponse;

class PublicAcademicYearController extends Controller
{
    /**
     * Get a list of academic years to populate the login dropdown.
     */
    public function listForLogin(): JsonResponse
    {
        // Ambil semua Tahun Ajaran, hanya kolom 'id' dan 'name' yang diperlukan
        // Diurutkan dari yang terbaru/tertinggi untuk ditampilkan pertama
        $academicYears = AcademicYear::orderBy('start', 'desc')
            ->select('id', 'name')
            ->get();

        return response()->json([
            'academic_years' => $academicYears,
        ]);
    }
}
