<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Student;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Models\CourseTurnStudent;

class SharedController extends Controller
{
    //
    public function getEnrolledYears()
    {
        $min_year = (new \Carbon\Carbon(CourseTurnStudent::where('created_at', '!=', null)->orderBy('created_at')->firstOrFail()->created_at))->year;
        $max_year = (new \Carbon\Carbon(Student::where('created_at', '!=', null)->orderByDesc('created_at')->firstOrFail()->created_at))->year;

        return response()->json([
            'min_year' => $min_year,
            'max_year' => $max_year,
        ]);
    }

    public function getBanks()
    {
        $banks = Bank::get();

        return response()->json($banks);
    }

    public function getDepartments()
    {
        $departments = Department::orderBy('name')->get();
        return response()->json($departments);
    }
}
