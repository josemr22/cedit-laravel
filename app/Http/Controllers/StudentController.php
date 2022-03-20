<?php

namespace App\Http\Controllers;

use App\Models\Fee;
use App\Models\Bank;
use App\Models\Damping;
use App\Models\Payment;
use App\Models\Student;
use App\Models\CourseTurn;
use App\Models\Department;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\CourseTurnStudent;
use App\Models\Installment;

class StudentController extends Controller
{
    // Informes
    public function index()
    {
        //
        $informs = Student::with('department', 'registered_by', 'course_turn.turn', 'course')->orderByDesc('created_at')->get();
        return response()->json($informs);
    }

    private function createStudent($request)
    {
        $data = $request->validate([
            'name' => 'required',
            'dni' => 'required',
            'email' => 'required',
            'department_id' => 'required',
            'address' => 'required',
            'phone' => 'nullable',
            'cellphone' => 'required',
            'date_of_birth' => 'required',
            'observation' => 'nullable',
            'course_id' => 'required',
            'course_turn_id' => 'required',
            'registered_by' => 'required',
        ]);

        $data['date_of_birth'] = new \Carbon\Carbon($data['date_of_birth']);

        return Student::create($data);
    }

    private function updateStudent($id, $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'dni' => 'required',
            'email' => 'required',
            'department_id' => 'required',
            'address' => 'required',
            'phone' => 'nullable',
            'cellphone' => 'required',
            'date_of_birth' => 'required',
            'observation' => 'nullable',
        ]);

        $student = Student::findOrFail($id);

        $student->update($data);

        return $student;
    }

    private function createTransaction($transactionForm)
    {
        $transaction = new Transaction();
        $transaction->user_id = $transactionForm['user_id'];
        $transaction->bank_id = $transactionForm['bank_id'];
        //TODO: When operation is mandatory?
        $bank = Bank::findOrFail($transactionForm['bank_id']);
        if ($bank->id != 1 && $bank->id != 6) {
            $transaction->operation = $transactionForm['operation'];
        }
        if ($bank->name == 'YAPE') {
            $transaction->name = $transactionForm['name'];
            $transaction->payment_date = new \Carbon\Carbon($transactionForm['payment_date']);
        }
        $transaction->save();
        return $transaction;
    }

    public function store(Request $request)
    {
        //
        $student = $this->createStudent($request);
        return response()->json($student);
    }

    public function updateStudentAndCourseTurn(CourseTurnStudent $courseTurnStudent, Request $request)
    {
        //
        $student = $this->updateStudent($courseTurnStudent->student->id, $request);

        $courseData = $request->validate([
            'course_turn_id' => 'required',
            'start_date' => 'required',
        ]);

        $courseTurnStudent->course_turn_id = $courseData['course_turn_id'];
        $courseTurnStudent->start_date =  new \Carbon\Carbon($courseData['start_date']);
        $courseTurnStudent->save();

        $studentWithCourse = CourseTurnStudent::with('courseTurn.course', 'courseTurn.turn', 'student.department', 'matriculator')
            ->findOrFail($courseTurnStudent->id);

        return response()->json($studentWithCourse);
    }

    public function getStudentsWithCourse()
    {
        //
        $courseTurnsIds = CourseTurn::when(request('course_id'), function ($query, $course_id) {
            $query->where('course_id', $course_id);
        })
            ->get()
            ->map(function ($courseTurn) {
                return $courseTurn->id;
            })
            ->toArray();

        $studentsWithCourse = CourseTurnStudent::with('courseTurn.course', 'courseTurn.turn', 'student.department', 'matriculator')
            ->when(request('start_date'), function ($query, $start_date) {
                $query->whereDate('start_date', $start_date);
            })
            ->when(request('anio'), function ($query, $anio) {
                $query->whereYear('created_at', $anio);
            })
            ->whereIn('course_turn_id', $courseTurnsIds)
            ->get();

        return response()->json($studentsWithCourse);

        // $students = Student::with('department', 'coursesGroup.course', 'coursesGroup.turn')
        //     ->whereIn('id', $studentIds)
        //     ->orderByDesc('created_at')->get();
        // return response()->json($students);
    }

    public function filter()
    {
        //
        $term = request('query');

        $studentsWithCourse = CourseTurnStudent::with('courseTurn.course', 'courseTurn.turn', 'student.department', 'matriculator')
            ->whereHas('student', function ($query) use ($term) {
                $query->where('name', 'like', "%$term%")
                    ->orWhere('dni', 'like', "%{$term}%");
            })
            ->get();

        return response()->json($studentsWithCourse);
    }

    public function showStudentWithCourse($id)
    {
        $studentsWithCourse = CourseTurnStudent::with('courseTurn.course', 'courseTurn.turn', 'student.department', 'matriculator')
            ->findOrFail($id);

        return response()->json($studentsWithCourse);
    }


    public function enroll(Request $request)
    {
        //
        if (!$request->input('student_id')) {
            $student = $this->createStudent($request);
        } else {
            $student = $this->updateStudent($request->input('student_id'), $request);
        }

        $courseTurnStudentData = $request->validate([
            'course_turn_id' => 'required',
            'start_date' => 'required',
            'enrolled_by' => 'required'
        ]);

        $courseTurnStudentData['start_date'] = new \Carbon\Carbon($courseTurnStudentData['start_date']);

        //Create Transaction
        $transactionForm = $request->input("transaction");

        $transaction = $this->createTransaction($transactionForm);

        //Payment
        $paymentForm = $request->input('payment');

        $payment = new Payment();
        $payment->type = $paymentForm['type'];
        $payment->observation = $paymentForm['observation'];
        $payment->amount = $paymentForm['amount'];
        if ($payment->type) {

            $payment->transaction_id = $transaction->id;
            $payment->voucher = "ABCDEFGH";
            $payment->save();
        } else {
            $payment->save();

            //Datos de Matrícula
            $installment = new Installment();
            $installment->type = 'm';
            $installment->amount = $paymentForm['enroll_amount'];
            $installment->payment_id = $payment->id;
            $installment->save();

            $damping = new Damping();
            $damping->amount = $paymentForm['pay_enroll_amount'];
            //TODO: CHANGE VOUCHER
            $damping->voucher = 'ABCDEFG';
            $damping->transaction_id = $transaction->id;
            $damping->installment_id = $installment->id;
            $damping->save();
            $installment->balance = floatval($paymentForm['enroll_amount']) - floatval($paymentForm['pay_enroll_amount']);
            $installment->save();
            //=================

            $installments = $paymentForm['installments'];
            $firstInstallment = true;
            foreach ($installments as $index => $item) {
                $installment = new Installment();
                $installment->number = $index + 1;
                $installment->type = 'c';
                $installment->amount = $item['amount'];
                $installment->payment_id = $payment->id;
                $installment->save();
                if ($firstInstallment) {
                    $damping = new Damping();
                    $damping->amount = $item['pay'];
                    //TODO: CHANGE VOUCHER
                    $damping->voucher = 'ABCDEFG';
                    $damping->transaction_id = $transaction->id;
                    $damping->installment_id = $installment->id;
                    $damping->save();
                    $installment->balance = floatval($item['amount']) - floatval($item['pay']);
                } else {
                    $installment->balance = $item['amount'];
                }
                $installment->save();
                $firstInstallment = false;
            }
        }

        //Enroll
        $enroll = new CourseTurnStudent();
        $enroll->student_id = $student->id;
        $enroll->course_turn_id = $courseTurnStudentData['course_turn_id'];
        $enroll->enrolled_by = $courseTurnStudentData['enrolled_by'];
        $enroll->payment_id = $payment->id;
        $enroll->start_date = new \Carbon\Carbon($courseTurnStudentData['start_date']);
        $enroll->save();

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
        $student = Student::with('department', 'registered_by', 'course_turn.turn', 'course')->findOrFail($student->id);
        return response()->json($student);
    }

    public function showPayments(CourseTurnStudent $courseTurnStudent)
    {
        //
        $payment = Payment::with('installments.dampings.transaction.bank', 'installments.dampings.transaction.responsable', 'courseTurnStudent.student', 'courseTurnStudent.courseTurn.course', 'courseTurnStudent.courseTurn.turn')
            ->findOrFail($courseTurnStudent->payment->id);
        return response()->json($payment);
    }

    public function payInstallment(Installment $installment, Request $request)
    {
        $data = $request->validate([
            'amount' => 'required',
        ]);

        $transactionForm = $request->input('transaction');

        $transaction = $this->createTransaction($transactionForm);

        $damping = new Damping();
        $damping->amount = $data['amount'];
        //TODO: Change voucher
        $damping->voucher = 'ABCDEFG';
        $damping->transaction_id = $transaction->id;
        $damping->installment_id = $installment->id;
        $damping->save();

        $installment->balance = floatval($installment->balance) - floatval($data['amount']);
        if ($installment->balance < 0) {
            $installment->balance = 0;
        }
        $installment->save();
    }

    public function getByOperation($operation, $bank_id)
    {
        $transaction = Transaction::with('bank', 'payment.courseTurnStudent.student', 'damping.installment.payment.courseTurnStudent.student', 'responsable')
            ->where('operation', $operation)
            ->where('bank_id', $bank_id)
            ->get();

        return response()->json($transaction);
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
}
