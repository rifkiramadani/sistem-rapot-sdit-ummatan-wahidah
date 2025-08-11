<?php

namespace App\Http\Controllers\Protected;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        // 1. Validate the 'per_page' input to allow only specific values.
        $request->validate([
            'per_page' => 'integer|in:1,50,100'
        ]);

        // 2. Get the per_page value from the request, defaulting to 10.
        $perPage = $request->input('per_page', 10);

        // 3. Paginate the results and append all query string parameters
        //    (like 'per_page') to the pagination links. This is the key step.
        $schools = School::with(['principal', 'currentAcademicYear'])
            ->paginate($perPage)
            ->appends($request->all());

        return Inertia::render('protected/schools/index', [
            'schools' => $schools,
        ]);
    }
}
