<?php

namespace App\Http\Controllers\Protected;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use App\Enums\PerPageEnum;
use App\Models\SchoolAcademicYear;
use App\Models\Subject;
use Inertia\Inertia;
use App\QueryFilters\Filter;
use App\QueryFilters\Sort;
use App\Support\QueryBuilder;
use Illuminate\Support\Facades\DB; // <-- Import DB
use Illuminate\Support\Facades\Gate; // <-- Import Gate for authorization
use Spatie\Activitylog\Facades\LogBatch;

class SubjectController extends Controller
{
    public function index(Request $request, SchoolAcademicYear $schoolAcademicYear)
    {
        // Authorization: Who can view the list of subjects?
        Gate::authorize('viewAny', Subject::class);

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

    public function show(SchoolAcademicYear $schoolAcademicYear, Subject $subject)
    {
        // Authorization: Who can view the details of this subject?
        Gate::authorize('view', $subject);

        return Inertia::render('protected/school-academic-years/subjects/show', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'subject' => $subject,
        ]);
    }

    public function create(SchoolAcademicYear $schoolAcademicYear)
    {
        // Authorization: Who can create a new subject?
        Gate::authorize('create', Subject::class);

        return Inertia::render('protected/school-academic-years/subjects/create', [
            'schoolAcademicYear' => $schoolAcademicYear
        ]);
    }

    public function store(Request $request, SchoolAcademicYear $schoolAcademicYear)
    {
        // Authorization: Who can create a new subject?
        Gate::authorize('create', Subject::class);

        // Validasi data subject
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        $schoolAcademicYear->subjects()->create([
            'name' => $validated['name'],
            'description' => $validated['description']
        ]);

        return redirect()->route('protected.school-academic-years.subjects.index', $schoolAcademicYear)
            ->with('success', 'Subject berhasil dibuat.');
    }

    public function edit(SchoolAcademicYear $schoolAcademicYear, Subject $subject)
    {
        // Authorization: Who can update this subject?
        Gate::authorize('update', $subject);

        return Inertia::render('protected/school-academic-years/subjects/edit', [
            'schoolAcademicYear' => $schoolAcademicYear,
            'subject' => $subject
        ]);
    }

    public function update(Request $request, SchoolAcademicYear $schoolAcademicYear, Subject $subject)
    {
        // Authorization: Who can update this subject?
        Gate::authorize('update', $subject);

        // Validasi data subject
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        $subject->update([
            'name' => $validated['name'],
            'description' => $validated['description']
        ]);

        return redirect()->route('protected.school-academic-years.subjects.index', $schoolAcademicYear)
            ->with('success', 'Subject berhasil diubah.');
    }

    public function destroy(SchoolAcademicYear $schoolAcademicYear, Subject $subject)
    {
        // Authorization: Who can delete this subject?
        Gate::authorize('delete', $subject);

        $subject->delete();

        return redirect()->route('protected.school-academic-years.subjects.index', $schoolAcademicYear)
            ->with('success', 'Subject berhasil dihapus.');
    }

    public function bulkDestroy(Request $request, SchoolAcademicYear $schoolAcademicYear)
    {
        // Authorization: Who can bulk delete subjects?
        Gate::authorize('bulkDelete', Subject::class);

        // Validasi bahwa 'ids' ada, merupakan sebuah array, dan setiap ID ada di tabel subject
        $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['exists:subjects,id'],
        ]);

        LogBatch::startBatch();

        DB::transaction(function () use ($request) {
            // Ambil semua model teacher yang akan dihapus, beserta relasi user-nya
            $subjects = Subject::whereIn('id', $request->input('ids'))->get();

            // Lakukan perulangan dan hapus user yang terkait
            foreach ($subjects as $subject) {
                if ($subject) {
                    $subject->delete();
                } else {
                    // hapus subject saja
                    $subject->delete();
                }
            }
        });

        LogBatch::endBatch();

        return redirect()->route('protected.school-academic-years.subjects.index', $schoolAcademicYear)
            ->with('success', 'Data Subject yang dipilih berhasil dihapus.');
    }
}
