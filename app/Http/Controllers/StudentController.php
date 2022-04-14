<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Damping;
use App\Models\Payment;
use App\Models\Student;
use App\Models\CourseTurn;
use App\Models\Installment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\CourseTurnStudent;
use App\Models\PayDetail;
use App\Models\SaleType;
use App\Traits\Helper;

class StudentController extends Controller
{
    use Helper;
    // Informes
    public function index()
    {
        $informs = Student::query()
            ->with('department', 'registered_by', 'course_turn.turn', 'course')
            ->when(request('onlyNotEnrolled'), function ($query) {
                return $query->doesntHave('course_turn_student');
            })
            ->orderByDesc('created_at')->get();
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
            // 'course_turn_id' => 'required',
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
    }

    public function filter()
    {
        //
        $term = request('query');

        $studentsWithCourse = CourseTurnStudent::with('courseTurn.course', 'courseTurn.turn', 'student.department', 'matriculator')
            ->whereHas('student', function ($query) use ($term) {
                $query->where('name', 'like', "%$term%")
                    ->orWhere('dni', 'like', "%{$term}%");
            })->take(10)
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
        $payment->save();

        $cts = CourseTurnStudent::findOrFail($courseTurnStudentData['course_turn_id']);
        $courseName = strtoupper($cts->courseTurn->course->name);
        if ($payment->type) {
            $this->createInstallmentAndDampingForEnroll('m', $paymentForm['amount'], $paymentForm['amount'], $payment->id, $transaction->id);
            $payDetail = [
                [
                    'label' => "Pago al contado en el curso de $courseName",
                    'amount' => $paymentForm['amount']
                ]
            ];
        } else {
            $this->createInstallmentAndDampingForEnroll('m', $paymentForm['enroll_amount'], $paymentForm['pay_enroll_amount'], $payment->id, $transaction->id);
            //=================
            $payDetail = [];
            if (floatval($paymentForm['enroll_amount']) == floatval($paymentForm['pay_enroll_amount'])) {
                $label = "Pago al contado en el curso de $courseName";
            } else {
                $label = "Pago parcial de matricula en el curso de $courseName";
            }
            array_push($payDetail, [
                'amount' => $paymentForm['pay_enroll_amount'],
                'label' => $label
            ]);
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
                    $damping->transaction_id = $transaction->id;
                    $damping->installment_id = $installment->id;
                    $damping->save();
                    $installment->balance = floatval($item['amount']) - floatval($item['pay']);
                    if (floatval($item['amount']) == floatval($item['pay'])) {
                        $label = " Pago completo de primera mensualidad en el curso de $courseName";
                    } else {
                        $label = "Pago parcial de primera mensualidad en el curso de $courseName";
                    }
                    array_push($payDetail, [
                        'amount' => floatval($item['pay']),
                        'label' => $label
                    ]);
                } else {
                    $installment->balance = $item['amount'];
                }
                $installment->save();
                $firstInstallment = false;
            }
        }
        //createPayDetail
        foreach ($payDetail as $value) {
            PayDetail::create([
                'amount' => $value['amount'],
                'label' => $value['label'],
                'transaction_id' => $transaction->id,
            ]);
        }

        //Enroll
        $enroll = new CourseTurnStudent();
        $enroll->student_id = $student->id;
        $enroll->course_turn_id = $courseTurnStudentData['course_turn_id'];
        $enroll->enrolled_by = $courseTurnStudentData['enrolled_by'];
        $enroll->payment_id = $payment->id;
        $enroll->start_date = new \Carbon\Carbon($courseTurnStudentData['start_date']);
        $enroll->save();

        $studentArr = [
            'name' => strtoupper($student->name),
            'address' => $student->address,
            'email' => $student->email,
        ];

        if ($transactionForm['voucher_type'] == 'B') {
            $sunat_response = $this->sendToSunat($transaction->id, $studentArr, $payDetail);
        } else {
            $sunat_response = null;
        }

        $transaction_response = [
            'transaction' => $transaction,
            'sunat_response' => $sunat_response,
        ];

        return response()->json($transaction_response);
    }

    private function createInstallmentAndDampingForEnroll($type, $enrollAmount, $dampingAmount, $paymentId, $transactionId)
    {
        $installment = new Installment();
        $installment->type = $type;
        $installment->amount = $enrollAmount;
        $installment->payment_id = $paymentId;
        $installment->save();

        $damping = new Damping();
        $damping->amount = $dampingAmount;
        $damping->transaction_id = $transactionId;
        $damping->installment_id = $installment->id;
        $damping->save();
        $installment->balance = floatval($enrollAmount) - floatval($dampingAmount);
        $installment->save();
    }

    public function storeSale(Request $request)
    {
        //Create Transaction
        $transactionForm = $request->input("transaction");

        $transaction = $this->createTransaction($transactionForm);

        //Payment
        $paymentForm = $request->input('payment');

        $payment = new Payment();
        $amount = floatval($paymentForm['amount']);
        $pay_amount = floatval($paymentForm['pay_amount']);
        $paymentType = 1;
        if ($amount !== $pay_amount) {
            $paymentType = 0;
        }
        $payment->type = $paymentType;
        $payment->observation = $paymentForm['observation'];
        $payment->amount = $amount;
        $payment->save();

        $type = $request->input('type');

        $this->createInstallmentAndDampingForEnroll($type, $amount, $pay_amount, $payment->id, $transaction->id);

        $payDetail = [];
        $type_label = SaleType::getList()[$type]['label'];

        array_push($payDetail, [
            'amount' => $amount,
            'label' => "Pago de $type_label"
        ]);

        //createPayDetail
        foreach ($payDetail as $value) {
            PayDetail::create([
                'amount' => $value['amount'],
                'label' => $value['label'],
                'transaction_id' => $transaction->id,
            ]);
        }

        //Create Sale
        $sale = new Sale();
        $sale->type = $type;
        $sale->state = $request->input('state');
        $sale->payment_id = $payment->id;
        $sale->user_id = $transactionForm['user_id'];
        $sale->course_turn_student_id = $request->input('course_turn_student_id');
        $sale->save();

        $student = $sale->course_turn_student->student;

        $studentArr = [
            'name' => strtoupper($student->name),
            'address' => $student->address,
            'email' => $student->email,
        ];


        if ($transactionForm['voucher_type'] == 'B') {
            $sunat_response = $this->sendToSunat($transaction->id, $studentArr, $payDetail);
        } else {
            $sunat_response = null;
        }

        $transaction_response = [
            'transaction' => $transaction,
            'sunat_response' => $sunat_response,
        ];

        return response()->json($transaction_response);
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

    public function showPayments(String $type, $id)
    {
        //
        switch ($type) {
            case 'course':
                $paymentId = CourseTurnStudent::findOrFail($id)->payment_id;
                break;
            case 'sale':
                $paymentId = Sale::findOrFail($id)->payment_id;
                break;

            default:
                abort(400);
                break;
        }
        $payment = Payment::with('installments.dampings.transaction.bank', 'installments.dampings.transaction.responsable', 'courseTurnStudent.student', 'courseTurnStudent.courseTurn.course', 'courseTurnStudent.courseTurn.turn', 'sale.seller', 'sale.course_turn_student.courseTurn.course', 'sale.course_turn_student.courseTurn.turn', 'sale.course_turn_student.student',)
            ->findOrFail($paymentId);
        return response()->json($payment);
    }

    public function getByOperation($operation, $bank_id)
    {
        $transaction = Transaction::with('bank', 'dampings.installment.payment.courseTurnStudent.student', 'responsable')
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
