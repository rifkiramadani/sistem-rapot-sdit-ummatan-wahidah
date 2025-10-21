<?php

namespace App\Http\Controllers\Protected;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\SchoolAcademicYear;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Summative;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the main dashboard with overall school information.
     */
    public function index(Request $request)
    {
        // Get the first (and only) school
        $school = School::with(['principal', 'currentAcademicYear'])->first();

        if (!$school) {
            return Inertia::render('protected/dashboard/index', [
                'school' => null,
                'dashboardData' => null,
                'error' => 'No school data found'
            ]);
        }

        // Get all school academic years for this school
        $schoolAcademicYears = $school->schoolAcademicYears()
            ->with('academicYear')
            ->withCount(['students', 'teachers', 'classrooms', 'subjects'])
            ->join('academic_years', 'school_academic_years.academic_year_id', '=', 'academic_years.id')
            ->orderBy('academic_years.start', 'desc')
            ->select('school_academic_years.*')
            ->get();

        // Calculate overall statistics across all academic years
        $totalStudents = Student::whereIn('school_academic_year_id', $schoolAcademicYears->pluck('id'))->count();
        $totalTeachers = Teacher::whereIn('school_academic_year_id', $schoolAcademicYears->pluck('id'))->count();
        $totalClassrooms = Classroom::whereIn('school_academic_year_id', $schoolAcademicYears->pluck('id'))->count();
        $totalSubjects = Subject::whereIn('school_academic_year_id', $schoolAcademicYears->pluck('id'))->count();

        // Calculate total summatives across all academic years
        $totalSummatives = 0;
        foreach ($schoolAcademicYears as $schoolAcademicYear) {
            $totalSummatives += Summative::whereHas('classroomSubject.classroom', function($query) use ($schoolAcademicYear) {
                $query->where('school_academic_year_id', $schoolAcademicYear->id);
            })->count();
        }

        // Prepare chart data for academic years overview
        $academicYearsData = $schoolAcademicYears->map(function ($say) {
            $summativeCount = Summative::whereHas('classroomSubject.classroom', function($query) use ($say) {
                $query->where('school_academic_year_id', $say->id);
            })->count();

            return [
                'id' => $say->id,
                'year' => $say->academicYear->name ?? 'Unknown',
                'academic_year_name' => $say->academicYear->name ?? 'Unknown',
                'students_count' => $say->students_count,
                'teachers_count' => $say->teachers_count,
                'classrooms_count' => $say->classrooms_count,
                'subjects_count' => $say->subjects_count,
                'summatives_count' => $summativeCount,
            ];
        });

        // Get distribution data for pie charts
        $studentsByAcademicYear = $academicYearsData->map(function ($data) {
            return [
                'name' => $data['academic_year_name'],
                'value' => $data['students_count']
            ];
        });

        $teachersByAcademicYear = $academicYearsData->map(function ($data) {
            return [
                'name' => $data['academic_year_name'],
                'value' => $data['teachers_count']
            ];
        });

        // Calculate growth trends (compare consecutive years)
        $growthTrends = [];
        for ($i = 1; $i < $academicYearsData->count(); $i++) {
            $current = $academicYearsData[$i];
            $previous = $academicYearsData[$i - 1];

            $growthTrends[] = [
                'year' => $current['academic_year_name'],
                'students_growth' => $previous['students_count'] > 0
                    ? (($current['students_count'] - $previous['students_count']) / $previous['students_count']) * 100
                    : 0,
                'teachers_growth' => $previous['teachers_count'] > 0
                    ? (($current['teachers_count'] - $previous['teachers_count']) / $previous['teachers_count']) * 100
                    : 0,
                'classrooms_growth' => $previous['classrooms_count'] > 0
                    ? (($current['classrooms_count'] - $previous['classrooms_count']) / $previous['classrooms_count']) * 100
                    : 0,
            ];
        }

        $dashboardData = [
            'school_info' => [
                'name' => $school->name,
                'npsn' => $school->npsn,
                'address' => $school->address,
                'website' => $school->website,
                'email' => $school->email,
                'principal' => $school->principal ? $school->principal->name : null,
                'current_academic_year' => $school->currentAcademicYear ? $school->currentAcademicYear->name : null,
            ],
            'overview_stats' => [
                'total_academic_years' => $schoolAcademicYears->count(),
                'total_students' => $totalStudents,
                'total_teachers' => $totalTeachers,
                'total_classrooms' => $totalClassrooms,
                'total_subjects' => $totalSubjects,
                'total_summatives' => $totalSummatives,
            ],
            'academic_years_data' => $academicYearsData,
            'chart_data' => [
                'students_by_academic_year' => $studentsByAcademicYear,
                'teachers_by_academic_year' => $teachersByAcademicYear,
                'growth_trends' => $growthTrends,
                'year_comparison' => $academicYearsData->map(function ($data) {
                    return [
                        'year' => $data['academic_year_name'],
                        'students' => $data['students_count'],
                        'teachers' => $data['teachers_count'],
                        'classrooms' => $data['classrooms_count'],
                        'subjects' => $data['subjects_count'],
                        'summatives' => $data['summatives_count'],
                    ];
                }),
            ],
        ];

        return Inertia::render('protected/dashboard/index', [
            'school' => $school,
            'dashboardData' => $dashboardData,
        ]);
    }
}