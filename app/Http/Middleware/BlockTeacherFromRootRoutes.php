<?php

namespace App\Http\Middleware;

use App\Enums\RoleEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
            // Redirect teachers to their school academic year selection or dashboard
            // For now, we'll abort with 403 Forbidden
            abort(403, 'Teachers are not authorized to access root routes. Please access through your assigned academic year dashboard.');
        }

        return $next($request);
    }
}
