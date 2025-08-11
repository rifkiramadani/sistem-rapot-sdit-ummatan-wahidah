<?php

namespace App\Http\Controllers\Protected;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SchoolController extends Controller
{
    public function index()
    {
        $schools = School::with(['principal', 'currentAcademicYear'])
            // ->paginate(10); // You can adjust the number per page
            ->all();

        return Inertia::render('protected/schools/index', [
            'schools' => $schools,
        ]);
    }
}
