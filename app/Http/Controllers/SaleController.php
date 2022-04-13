<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    //
    public function index()
    {
        $course_id = request('courseId');
        $sale_year = request('saleYear');
        $sale_type = request('saleType');

        $sales = Sale::query()
            ->with('seller', 'course_turn_student.courseTurn.course', 'course_turn_student.courseTurn.turn', 'course_turn_student.student', 'payment.installments')
            ->where('type', $sale_type)
            ->whereYear('created_at', $sale_year)
            ->whereHas('course_turn_student', function ($query) use ($course_id) {
                return $query->whereHas('courseTurn', function ($query) use ($course_id) {
                    return $query->whereHas('course', function ($query) use ($course_id) {
                        return $query->where('id', $course_id);
                    });
                });
            })
            ->get();
        return response()->json($sales);
    }

    public function getSaleYears()
    {
        $min_year = (new \Carbon\Carbon(Sale::where('created_at', '!=', null)->orderBy('created_at')->firstOrFail()->created_at))->year;
        $max_year = (new \Carbon\Carbon(Sale::where('created_at', '!=', null)->orderByDesc('created_at')->firstOrFail()->created_at))->year;

        return response()->json([
            'min_year' => $min_year,
            'max_year' => $max_year,
        ]);
    }
}
