<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use Illuminate\Http\Request;

class InstallmentController extends Controller
{
    //
    public function show(Installment $installment)
    {
        $installment = Installment::with('payment.courseTurnStudent.student', 'payment.courseTurnStudent.courseTurn.course', 'payment.courseTurnStudent.courseTurn.turn')->findOrFail($installment->id);
        return response()->json($installment);
    }
}
