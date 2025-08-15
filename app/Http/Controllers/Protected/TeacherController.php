<?php

namespace App\Http\Controllers\Protected;

use App\Http\Controllers\Controller;
use App\Models\SchoolAcademicYear;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TeacherController extends Controller
{
    public function index(Request $request, SchoolAcademicYear $schoolAcademicYear)
    {
        // 1. Validasi semua parameter request
        $request->validate([
            'per_page' => 'sometimes|integer|in:10,20,50,100',
            // Sesuaikan kolom yang bisa di-sort untuk model Teacher
            'sort_by' => 'sometimes|string|in:name,niy',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            'filter' => 'sometimes|array',
            'filter.q' => 'sometimes|string|nullable',
        ]);

        // 2. Ambil parameter dengan nilai default
        $perPage = $request->input('per_page', 10);
        // Default sort adalah 'name' (nama guru), secara ascending
        $sortBy = $request->input('sort_by', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');
        $searchQuery = $request->input('filter.q');

        // 3. Bangun query dasar dari relasi 'teachers'
        // Eager-load relasi 'user' untuk menampilkan data user (cth: email) secara efisien
        $query = $schoolAcademicYear->teachers()->with('user');

        // 4. Terapkan filtering (pencarian) jika ada searchQuery
        $query->when($searchQuery, function ($query, $search) {
            $searchLower = strtolower($search);
            // Gunakan grup 'where' agar semua kondisi pencarian (OR) terkurung dengan benar
            $query->where(function ($subQuery) use ($searchLower) {
                // Cari pada kolom 'name' dan 'niy' di tabel teachers
                $subQuery->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                    ->orWhereRaw('LOWER(niy) LIKE ?', ["%{$searchLower}%"])
                    // Gunakan 'orWhereHas' untuk mencari pada relasi 'user'
                    ->orWhereHas('user', function ($userQuery) use ($searchLower) {
                        // Cari pada kolom 'name' dan 'email' di tabel users
                        $userQuery->whereRaw('LOWER(name) LIKE ?', ["%{$searchLower}%"])
                            ->orWhereRaw('LOWER(email) LIKE ?', ["%{$searchLower}%"]);
                    });
            });
        });

        // 5. Terapkan sorting
        // Karena 'name' dan 'niy' ada di tabel 'teachers' itu sendiri, kita tidak perlu JOIN
        $query->orderBy($sortBy, $sortDirection);

        // 6. Lakukan paginasi dan tambahkan semua parameter query string
        $teachers = $query->paginate($perPage)->withQueryString();

        // 7. Kembalikan response ke view Inertia
        return Inertia::render('protected/school-academic-years/teachers/index', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'teachers' => $teachers,
        ]);
    }
}
