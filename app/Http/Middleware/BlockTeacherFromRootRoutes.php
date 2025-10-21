<?php

namespace App\Http\Middleware;

use App\Enums\RoleEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\SchoolAcademicYear;

class BlockTeacherFromRootRoutes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Block teachers from accessing root protected routes
        if ($user && $user->role && $user->role->name === RoleEnum::TEACHER->value) {
            // Try to find an academic year where this teacher is registered
            $teacherRecord = $user->teacher()->first();

            if ($teacherRecord && $teacherRecord->school_academic_year_id) {
                // Redirect to the teacher's assigned academic year dashboard
                return redirect()->route('protected.school-academic-years.dashboard.index', [
                    'schoolAcademicYear' => $teacherRecord->school_academic_year_id
                ]);
            }

            // If no academic year assignment found, redirect to login with a message
            return redirect()->route('login')
                ->with('error', 'Anda belum ditugaskan ke tahun ajaran manapun. Silakan hubungi administrator.');
        }

        return $next($request);
    }
}
