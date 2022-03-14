<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Damping;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Department;
use App\Models\Transaction;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        //
        $students = Student::where('signed_up', 1)->with('department', 'enrolled_by', 'course_turn.turn', 'course')
            ->when(request('course_id'), function ($query, $course_id) {
                $query->where('course_id', $course_id);
            })
            ->when(request('anio'), function ($query, $anio) {
                $query->whereYear('enrolled_at', $anio);
            })
            ->when(request('start_date'), function ($query, $start_date) {
                $query->whereYear('start_date', $start_date);
            })
            ->orderByDesc('enrolled_at')->get();
        return response()->json($students);
    }

    public function filter()
    {
        //
        $query = request('query');
        $students = Student::where('signed_up', 1)
            ->where('name', 'like', "%$query%")
            // ->orWhere('dni', 'like', "%{$request->query}%")
            // ->with('department', 'enrolled_by', 'course_turn.turn', 'course')
            ->orderByDesc('name')
            ->take(10)
            ->get();
        return response()->json($students);
    }

    public function indexInforms()
    {
        //
        $informs = Student::where('signed_up', 0)->with('department', 'registered_by')->orderByDesc('registered_at')->get();
        return response()->json($informs);
    }

    public function enroll(Student $student, Request $request)
    {
        //
        $data = $request->validate([
            'name' => 'required',
            'dni' => 'required',
            'email' => 'required',
            'department_id' => 'required',
            'address' => 'required',
            'phone' => 'required',
            'cellphone' => 'required',
            'observation' => 'required',
            'course_id' => 'required',
            'course_turn_id' => 'required',
            'start_date' => 'required',
            'enrolled_by' => 'required',
        ]);

        $data['enrolled_at'] = \Carbon\Carbon::now();
        $data['start_date'] = new \Carbon\Carbon($data['start_date']);
        $data['signed_up'] = 1;

        $student->update($data);

        //Payment
        $payment = new Payment();
        $paymentForm = $request->input('payment');

        $payment->type = $paymentForm['type'];
        $payment->observation = $paymentForm['observation'];
        $payment->student_id = $student->id;
        $payment->amount = $paymentForm['amount'];
        $payment->save();
        if ($payment->type) {

            $payment->amount_payable = $paymentForm['amount_payable'];
            $payment->save();

            $cuotas = $paymentForm['cuotas'];
            foreach ($cuotas as $item) {
                $cuota = new Fee();
                $cuota->amount = $item['amount'];
                $cuota->balance = $item['balance'];
                $cuota->payment_id = $payment->id;
                $payment->save();
            }
        }
        $transactionForm = $request->input("transaction");
        $transaction = new Transaction();
        $transaction->bank_id = $transactionForm['bank_id'];
        $transaction->operation = $transactionForm['operation'];
        $transaction->save();
        $payment->transaction_id = $transaction->id;

        return response()->json($student);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'dni' => 'required',
            'email' => 'required',
            'department_id' => 'required',
            'address' => 'required',
            'phone' => 'nullable',
            'cellphone' => 'required',
            'observation' => 'nullable',
            'course_id' => 'required',
            'course_turn_id' => 'required',
            'start_date' => 'required',
            'registered_by' => 'required',
            'enrolled_by' => 'required',
            // 'transaction' => 'required',
            // 'payment' => 'required',
        ]);

        $paymentData = $request->payment;
        $transactionData = $request->transaction;

        $data['enrolled_at'] = \Carbon\Carbon::now();
        $data['start_date'] = new \Carbon\Carbon($data['start_date']);
        $data['signed_up'] = 1;

        $student = Student::create($data);

        //Payment
        $payment = new Payment();

        $payment->type = $paymentData['payment_type'];
        $payment->observation = $paymentData['observation'];
        $payment->student_id = $student->id;
        $payment->amount = $paymentData['amount'];
        $payment->save();

        //if cash
        if ($payment->type == '1') {
            //TODO: CHANGE DEFAULT
            $payment->cash_voucher = 'ABCD1234';
            //createTransaction

            $transaction = new Transaction();
            $transaction->bank_id = $transactionData['bank_id'];
            $transaction->operation = $transactionData['operation'];
            $transaction->save();
            $payment->transaction_id = $transaction->id;
            $payment->save();
        } else {
            //if credit
            // $firstQuote->

            $transaction = new Transaction();
            $transaction->bank_id = $transactionData['bank_id'];
            $transaction->operation = $transactionData['operation'];
            $transaction->save();

            // Matricula
            $damping = new Damping();
            $damping->amount = $paymentData['amount_to_pay_enrollment'];
            //TODO: changeDefault
            $damping->voucher = "ABCD1234";
            $damping->type = 'm';
            $damping->transaction_id = $transaction->id;
            $damping->save();


            //Cuota1


            $damping = new Damping();
            $damping->amount = $paymentData['quote1'];
            //TODO: changeDefault
            $damping->voucher = "ABCD1234";
            $damping->type = 'c';
            $damping->transaction_id = $transaction->id;
            $damping->save();

            $firstQuote = new Fee();
            $firstQuote->amount = $paymentData['quote1'];
            $firstQuote->balance = $paymentData['quote1'] - $paymentData['pay_quote1'];
            $firstQuote->late = 0;
            // $firstQuote->observation = $paymentData[''];
            $firstQuote->payment_id = $payment->id;
            $firstQuote->save();

            // $quotes = $paymentData['quotas'];
            //TODO: recorrer cuotas

            // foreach ($quotes as $item) {
            //     $cuota = new Fee();
            //     $cuota->amount = $item['amount'];
            //     $cuota->balance = $item['balance'];
            //     $cuota->payment_id = $payment->id;
            //     $payment->save();
            // }
        }

        return response()->json($student);
    }

    public function storeInforms(Request $request)
    {
        //
        $data = $request->validate([
            'name' => 'required',
            'dni' => 'required',
            'email' => 'required',
            'department_id' => 'required',
            'address' => 'required',
            'phone' => 'nullable',
            'cellphone' => 'required',
            'observation' => 'required',
            'course_id' => 'required',
            'registered_by' => 'required',
        ]);

        $data['registered_at'] = \Carbon\Carbon::now();

        $student = Student::create($data);
        return response()->json($student);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function show(Student $student)
    {
        //
    }

    public function showPayments(Student $student)
    {
        //
        $payment = Payment::where('student_id', $student->id)->with('transaction')->with('quotes')->firstOrFail();
        return response()->json($payment);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function edit(Student $student)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Student  $student
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Student $student)
    {
        //
        $data = $request->validate([
            'name' => 'required',
            'dni' => 'required',
            'email' => 'required',
            'department_id' => 'required',
            'address' => 'required',
            'phone' => 'nullable',
            'cellphone' => 'required',
            'course_id' => 'required',
            'course_turn_id' => 'required',
            'start_date' => 'required',
        ]);

        $student->update($data);
        return response()->json($student);
    }

    public function destroy(Student $student)
    {
        //
        $student->delete();
        return response()->json();
    }

    public function destroyInform(Student $student)
    {
        //
        if ($student->state == 1) {
            abort(500);
        }
        $student->delete();
        return response()->json();
    }

    public function getDepartments()
    {
        $departments = Department::orderBy('name')->get();
        return response()->json($departments);
    }
}
