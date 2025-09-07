<?php

namespace App\Http\Controllers\Protected;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use App\Enums\PerPageEnum;
use App\Models\SchoolAcademicYear;
use Inertia\Inertia;
use App\QueryFilters\Filter;
use App\QueryFilters\Sort;
use App\Support\QueryBuilder;

class SubjectController extends Controller
{
    public function index(Request $request, SchoolAcademicYear $schoolAcademicYear)
    {
        // 1. Validasi semua parameter request
        $request->validate([
            'per_page' => ['sometimes', 'string', Rule::in(PerPageEnum::values())],
            // Sesuaikan kolom yang bisa di-sort untuk model Teacher
            'sort_by' => 'sometimes|string|in:name,id',
            'sort_direction' => 'sometimes|string|in:asc,desc',
            'filter' => 'sometimes|array',
            'filter.q' => 'sometimes|string|nullable',
        ]);

        $subjects = QueryBuilder::for($schoolAcademicYear->subjects())
            ->through([
                Filter::class,
                Sort::class,
            ])
            ->paginate();

        // 7. Kembalikan response ke view Inertia
        return Inertia::render('protected/school-academic-years/subjects/index', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'subjects' => $subjects,
        ]);
    }

    public function create(SchoolAcademicYear $schoolAcademicYear)
    {
        return Inertia::render('protected/school-academic-years/subjects/create', [
            'schoolAcademicYear' => $schoolAcademicYear
        ]);
    }
}
