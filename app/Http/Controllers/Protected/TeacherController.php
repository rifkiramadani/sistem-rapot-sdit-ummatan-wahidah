<?php

namespace App\Http\Controllers\Protected;

use App\Enums\PerPageEnum;
use App\Http\Controllers\Controller;
use App\Models\SchoolAcademicYear;
use App\QueryFilters\Filter;
use App\QueryFilters\Sort;
use App\Support\QueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TeacherController extends Controller
{
    public function index(Request $request, SchoolAcademicYear $schoolAcademicYear)
    {
        // 1. Validasi semua parameter request
        $request->validate([
            'per_page' => ['sometimes', 'string', Rule::in(PerPageEnum::values())],
            // Sesuaikan kolom yang bisa di-sort untuk model Teacher
            'sort_by' => 'sometimes|string|in:name,niy',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            'filter' => 'sometimes|array',
            'filter.q' => 'sometimes|string|nullable',
        ]);

        $teachers = QueryBuilder::for($schoolAcademicYear->teachers()->with('user'))
            ->through([
                Filter::class,
                Sort::class,
            ])
            ->paginate();

        // 7. Kembalikan response ke view Inertia
        return Inertia::render('protected/school-academic-years/teachers/index', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'teachers' => $teachers,
        ]);
    }
}
