<?php

namespace App\Http\Controllers\Protected\SchoolAcademicYear;

use App\Http\Controllers\Controller;
use App\Models\SchoolAcademicYear;
use App\Models\Summative;
use App\Models\StudentSummative;
use App\Models\ClassroomSubject;
use App\Models\Student;
use App\Models\ClassroomStudent;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    /**
     * Display the school academic year dashboard with detailed information.
     */
    public function index(Request $request, SchoolAcademicYear $schoolAcademicYear)
    {
        // Authorization: Who can access this academic year's dashboard?
        Gate::authorize('view', $schoolAcademicYear);

        try {
            Log::info('Loading school-academic-year dashboard', [
                'schoolAcademicYearId' => $schoolAcademicYear->id,
                'academicYear' => $schoolAcademicYear->academicYear?->name,
            ]);

            // Check if current user is a teacher and get their teacher data
            $user = $request->user();
            $isTeacher = $user && $user->role && $user->role->name === \App\Enums\RoleEnum::TEACHER->value;

            if ($isTeacher) {
                // Get teacher's data for this academic year
                $teacherRecord = $user->teacher()
                                     ->where('school_academic_year_id', $schoolAcademicYear->id)
                                     ->first();

                if (!$teacherRecord) {
                    return Inertia::render('protected/school-academic-years/dashboard/index', [
                        'schoolAcademicYear' => $schoolAcademicYear,
                        'dashboardData' => null,
                        'error' => 'You are not registered as a teacher in this academic year.',
                    ]);
                }

                // Load teacher's classroom relationships
                $teacherClassrooms = $teacherRecord->classrooms()
                                          ->with(['classroomStudents.student', 'classroomSubjects.subject'])
                                          ->get();

                // Load all necessary relationships (limited for teachers)
                $schoolAcademicYear->load([
                    'school.principal',
                    'academicYear',
                ]);

                // Filter statistics to only include teacher's classrooms
                $teacherClassroomIds = $teacherClassrooms->pluck('id');
                $teacherStudentIds = $teacherClassrooms->flatMap(function ($classroom) {
                    return $classroom->classroomStudents->pluck('student_id');
                });
                $teacherSubjectIds = $teacherClassrooms->flatMap(function ($classroom) {
                    return $classroom->classroomSubjects->pluck('subject_id');
                });

                // Calculate teacher-specific statistics
                $totalStudents = $teacherStudentIds->count();
                $totalClassrooms = $teacherClassrooms->count();
                $totalSubjects = $teacherSubjectIds->count();
                $totalTeachers = 1; // Teacher sees only themselves as "total teachers" in their view

                // Calculate gender distribution for teacher's students
                $teacherStudents = \App\Models\Student::whereIn('id', $teacherStudentIds)->get();
                $genderDistribution = [
                    'male' => $teacherStudents->where('gender', \App\Enums\GenderEnum::MALE->value)->count(),
                    'female' => $teacherStudents->where('gender', \App\Enums\GenderEnum::FEMALE->value)->count(),
                ];

                // Calculate average age (simplified)
                $averageAge = 10; // Default average age for elementary school students

                // Get teacher's classroom distribution
                $classroomDistribution = $teacherClassrooms->map(function ($classroom) {
                    return [
                        'name' => $classroom->name,
                        'students_count' => $classroom->classroomStudents->count(),
                        'teacher' => $classroom->teacher?->name ?? 'No teacher assigned',
                    ];
                });

                // Calculate summative statistics for teacher's classrooms only
                $totalSummatives = Summative::whereHas('classroomSubject.classroom', function ($query) use ($teacherClassroomIds) {
                    $query->whereIn('classroom_id', $teacherClassroomIds);
                })->count();

                $completedSummatives = StudentSummative::whereHas('summative.classroomSubject.classroom', function ($query) use ($teacherClassroomIds) {
                    $query->whereIn('classroom_id', $teacherClassroomIds);
                })->count();

                // Calculate average score for teacher's students
                $studentSummatives = StudentSummative::whereHas('summative.classroomSubject.classroom', function ($query) use ($teacherClassroomIds) {
                    $query->whereIn('classroom_id', $teacherClassroomIds);
                })->whereNotNull('value')->get();

                $averageScore = $studentSummatives->count() > 0
                    ? round($studentSummatives->avg('value'), 2)
                    : 0;

                $completionRate = $totalSummatives > 0 ? round(($completedSummatives / $totalSummatives) * 100, 1) : 0;
            } else {
                // Load all necessary relationships for management users
                $schoolAcademicYear->load([
                    'school.principal',
                    'academicYear',
                    'students',
                    'teachers.user',
                    'classrooms.teacher',
                    'subjects'
                ]);

                // Calculate statistics
                $totalStudents = $schoolAcademicYear->students()->count();
                $totalTeachers = $schoolAcademicYear->teachers()->count();
                $totalClassrooms = $schoolAcademicYear->classrooms()->count();
                $totalSubjects = $schoolAcademicYear->subjects()->count();

                // Calculate gender distribution
                $genderDistribution = [
                    'male' => $schoolAcademicYear->students()->where('gender', \App\Enums\GenderEnum::MALE->value)->count(),
                    'female' => $schoolAcademicYear->students()->where('gender', \App\Enums\GenderEnum::FEMALE->value)->count(),
                ];

                // Calculate average age (simplified)
                $averageAge = 10; // Default average age for elementary school students

                // Get classroom distribution
                $classroomDistribution = $schoolAcademicYear->classrooms()->get()->map(function ($classroom) {
                    // Count students for this classroom
                    $studentCount = \App\Models\ClassroomStudent::where('classroom_id', $classroom->id)->count();

                    return [
                        'name' => $classroom->name,
                        'students_count' => $studentCount,
                        'teacher' => $classroom->teacher?->name ?? 'No teacher assigned',
                    ];
                });

                // Calculate summative statistics (simplified)
                $totalSummatives = Summative::whereHas('classroomSubject.classroom', function ($query) use ($schoolAcademicYear) {
                    $query->where('school_academic_year_id', $schoolAcademicYear->id);
                })->count();

                $completedSummatives = StudentSummative::whereHas('summative.classroomSubject.classroom', function ($query) use ($schoolAcademicYear) {
                    $query->where('school_academic_year_id', $schoolAcademicYear->id);
                })->count();

                // Calculate average score
                $studentSummatives = StudentSummative::whereHas('summative.classroomSubject.classroom', function ($query) use ($schoolAcademicYear) {
                    $query->where('school_academic_year_id', $schoolAcademicYear->id);
                })->whereNotNull('value')->get();

                $averageScore = $studentSummatives->count() > 0
                    ? round($studentSummatives->avg('value'), 2)
                    : 0;

                $completionRate = $totalSummatives > 0 ? round(($completedSummatives / $totalSummatives) * 100, 1) : 0;
            }

            // Prepare chart data
            $classroomChartData = $classroomDistribution->map(function ($classroom) {
                return [
                    'name' => $classroom['name'],
                    'students' => $classroom['students_count'],
                ];
            });

            $genderChartData = [
                ['name' => 'Laki-laki', 'value' => $genderDistribution['male']],
                ['name' => 'Perempuan', 'value' => $genderDistribution['female']],
            ];

            $dashboardData = [
                'school_info' => [
                    'name' => $schoolAcademicYear->school->name,
                    'npsn' => $schoolAcademicYear->school->npsn,
                    'address' => $schoolAcademicYear->school->address,
                    'principal' => $schoolAcademicYear->school->principal?->name ?? 'Not assigned',
                    'academic_year' => $schoolAcademicYear->academicYear->name,
                    'year' => $schoolAcademicYear->academicYear?->year ?? $schoolAcademicYear->academicYear->name,
                ],
                'overview_stats' => [
                    'total_students' => $totalStudents,
                    'total_teachers' => $totalTeachers,
                    'total_classrooms' => $totalClassrooms,
                    'total_subjects' => $totalSubjects,
                    'total_summatives' => $totalSummatives,
                    'completed_summatives' => $completedSummatives,
                ],
                'student_demographics' => [
                    'gender_distribution' => $genderDistribution,
                    'average_age' => $averageAge,
                ],
                'performance_stats' => [
                    'average_score' => $averageScore,
                    'completion_rate' => $completionRate,
                    'total_assessments' => $totalSummatives,
                    'completed_assessments' => $completedSummatives,
                ],
                'chart_data' => [
                    'classroom_distribution' => $classroomChartData,
                    'gender_distribution' => $genderChartData,
                ],
                'classroom_details' => $classroomDistribution,
            ];

            return Inertia::render('protected/school-academic-years/dashboard/index', [
                'schoolAcademicYear' => $schoolAcademicYear,
                'dashboardData' => $dashboardData,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load school-academic-year dashboard', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'schoolAcademicYearId' => $schoolAcademicYear->id,
            ]);

            return Inertia::render('protected/school-academic-years/dashboard/index', [
                'schoolAcademicYear' => $schoolAcademicYear,
                'dashboardData' => null,
                'error' => 'Failed to load dashboard data: ' . $e->getMessage(),
            ]);
        }
    }
}
