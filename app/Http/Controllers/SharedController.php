<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Bank;
use App\Models\Student;
use App\Models\Department;
use App\Models\CourseTurnStudent;
use App\Models\Payment;
use App\Models\Transaction;

class SharedController extends Controller
{
    //
    public function getMenu()
    {
        return response()->json(Menu::getList());
    }

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

    public function getDashboardData()
    {
        $pendingInscriptions = Student::query()
            ->with('department', 'registered_by', 'course_turn.turn', 'course')
            ->doesntHave('course_turn_student')
            ->orderByDesc('created_at')->get()->count();

        $registeredStudents = CourseTurnStudent::count();

        $recordedVouchers = Transaction::count();

        $credits = Payment::where('type', 0)->count();

        $resp = [
            'pendingInscriptions' => $pendingInscriptions,
            'registeredStudents' => $registeredStudents,
            'recordedVouchers' => $recordedVouchers,
            'credits' => $credits,
        ];

        return response()->json($resp);
    }
}
