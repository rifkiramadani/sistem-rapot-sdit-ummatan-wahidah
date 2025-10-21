<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;
use App\Models\SchoolAcademicYear;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => function () use ($request) {
                    $user = $request->user();

                    if (!$user) {
                        return null;
                    }

                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role ? [
                            'id' => $user->role->id,
                            'name' => $user->role->name,
                        ] : null,
                    ];
                },
            ],
            'ziggy' => fn(): array => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'queryParams' => $request->query(),
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
            ],
            'app' => [
                'env' => app()->environment(), // Membagikan 'local', 'production', dll.
            ],
            'currentSchoolAcademicYear' => function () use ($request) {
                // Check if we're on a school academic year route
                $schoolAcademicYear = $request->route('schoolAcademicYear');

                if ($schoolAcademicYear) {
                    // Load the academic year relationship
                    $schoolAcademicYear->load('academicYear');

                    return [
                        'id' => $schoolAcademicYear->id,
                        'academic_year' => [
                            'name' => $schoolAcademicYear->academicYear->name,
                        ],
                    ];
                }

                return null;
            },
        ];
    }
}
