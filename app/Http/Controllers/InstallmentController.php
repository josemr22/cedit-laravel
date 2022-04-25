<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use Illuminate\Http\Request;

class InstallmentController extends Controller
{
    //
    public function show(Installment $installment)
    {
        $installment = Installment::with('payment.courseTurnStudent.student', 'payment.sale.course_turn_student.student', 'payment.courseTurnStudent.courseTurn.course', 'payment.courseTurnStudent.courseTurn.turn')->findOrFail($installment->id);
        return response()->json($installment);
    }

    public function createMora(Installment $installment, Request $request)
    {
        if ($installment->mora != 0) {
            abort(400);
        }
        $data = $request->validate([
            'mora' => 'required'
        ]);
        $mora = floatval($data['mora']);
        $installment->mora = $mora;
        $installment->balance = $installment->balance + $mora;
        $installment->save();

        return response()->json(true);
    }
}
