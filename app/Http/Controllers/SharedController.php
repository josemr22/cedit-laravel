<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Student;
use Illuminate\Http\Request;

class SharedController extends Controller
{
    //
    public function getEnrolledYears()
    {
        $min_year = (new \Carbon\Carbon(Student::where('enrolled_at', '!=', null)->orderBy('enrolled_at')->firstOrFail()->enrolled_at))->year;
        $max_year = (new \Carbon\Carbon(Student::where('enrolled_at', '!=', null)->orderByDesc('enrolled_at')->firstOrFail()->enrolled_at))->year;

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
}
