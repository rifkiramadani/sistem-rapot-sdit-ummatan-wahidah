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

class StudentController extends Controller
{
    public function index(Request $request, SchoolAcademicYear $schoolAcademicYear)
    {
        // Gate::authorize('viewAny', Student::class);

        $request->validate([
            'per_page' => ['sometimes', 'string', Rule::in(PerPageEnum::values())],
            'sort_by' => 'sometimes|string|in:name,nisn', // Kolom yang bisa di-sort
            'sort_direction' => 'sometimes|string|in:asc,desc',
            'filter' => 'sometimes|array',
            'filter.q' => 'sometimes|string|nullable',
        ]);

        $students = QueryBuilder::for($schoolAcademicYear->students()->with(['parent', 'guardian']))
            ->through([
                Filter::class,
                Sort::class,
            ])
            ->paginate();

        return Inertia::render('protected/school-academic-years/students/index', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'students' => $students,
        ]);
    }
}
