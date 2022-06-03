<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\User;
use App\Traits\Helper;
use App\Models\Damping;
use App\Models\SaleType;
use App\Models\Spending;
use App\Models\PayDetail;
use App\Models\Installment;
use App\Models\Transaction;
use App\Models\VoucherType;
use App\Models\VoucherState;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\App;
use Luecano\NumeroALetras\NumeroALetras;

class TillController extends Controller
{
    //
    use Helper;

    public function payInstallment(Installment $installment, Request $request)
    {
        $data = $request->validate([
            'amount' => 'required',
        ]);

        $transactionForm = $request->input('transaction');

        $transaction = $this->createTransaction($transactionForm);

        $damping = new Damping();
        $damping->amount = $data['amount'];
        $damping->transaction_id = $transaction->id;
        $damping->installment_id = $installment->id;
        $damping->save();

        $installment->balance = floatval($installment->balance) - floatval($data['amount']);
        if ($installment->balance < 0) {
            $installment->balance = 0;
        }
        $installment->save();

        $payment = $installment->payment;

        if ($payment->courseTurnStudent != null) {
            $student = $payment->courseTurnStudent->student;
        }
        if ($payment->sale != null) {
            $student = $payment->sale->course_turn_student->student;
        }

        $studentArr = [
            'name' => $transactionForm['voucher_type'] == 'F' ? strtoupper($transactionForm['razon_social']) : strtoupper($student->name),
            'address' => $transactionForm['voucher_type'] == 'F' ? $transactionForm['address'] : $student->address,
            'email' => $transactionForm['voucher_type'] == 'F' ? $transactionForm['email'] : $student->email,
            'num_doc' => $transactionForm['voucher_type'] == 'F' ? $transactionForm['ruc'] : $student->dni,
        ];
        $payDetail = [];

        
        if ($installment->type == 'm') {
            $courseName = $installment->payment->courseTurnStudent->courseTurn->course->name;
            $label = "Pago de matrícula del curso $courseName";
        } else if ($installment->type == 'c') {
            $courseName = $installment->payment->courseTurnStudent->courseTurn->course->name;
            $number_installment = $installment->number;
            $label = "Pago de mensualidad $number_installment del curso $courseName";
        } else {
            // $courseName = $installment->payment->sale->course_turn_student->courseTurn->course->name;
            $saleType = SaleType::getList()[$installment->type]['label'];
            $label = "Pago de $saleType";
        }

        array_push($payDetail, [
            'amount' => floatval($data['amount']),
            'label' => $label
        ]);

        //createPayDetail
        foreach ($payDetail as $value) {
            PayDetail::create([
                'amount' => $value['amount'],
                'label' => $value['label'],
                'transaction_id' => $transaction->id,
            ]);
        }

        if ($transactionForm['voucher_type'] == 'R') {
            $code = $this->getCode($transaction->voucher_type);
            $transaction->voucher = $code;
            $transaction->voucher_state = 'E';
            $transaction->save();
            $sunat_response = null;
        } else {
            $sunat_response = $this->sendToSunat($transaction->id, $studentArr, $payDetail);
        }

        $transaction_response = [
            'transaction' => $transaction,
            'sunat_response' => $sunat_response,
        ];

        return response()->json($transaction_response);
    }

    public function editInstallment(Installment $installment, Request $request)
    {
        $data = $request->validate([
            'amount' => 'required',
        ]);

        $diff = floatval($installment->amount) - floatval($data['amount']);

        $installment->amount = floatval($data['amount']);

        $installment->balance = floatval($installment->balance) - $diff;
        if ($installment->balance < 0) {
            $installment->balance = 0;
        }
        $installment->save();

        return response()->json(true);
    }

    public function getBankReport()
    {
        $from = request('from');
        $to = request('to');

        $banks = Bank::query()
            ->with('transactions.dampings')
            ->get();

        $resp = $banks->map(function ($b) use ($from, $to) {

            $total = 0;

            $transactions = $b->transactions()
                ->whereDate('created_at', '>=', $from)
                ->whereDate('created_at', '<=', $to)
                ->where('state', true)
                ->get();

            foreach ($transactions as $transaction) {
                foreach ($transaction->dampings  as $damping) {
                    $total = $total + $damping->amount;
                }
            }

            return [
                'bank' => [
                    "id" => $b->id,
                    "name" => $b->name,
                    "abbreviation" => $b->abbreviation,
                ],
                'amount' => $total
            ];
        });

        return response()->json($resp);
    }

    public function productionByUser()
    {
        $from = request('from');
        $to = request('to');

        $users = User::query()
            ->with('transactions_made')
            ->get();

        $resp = $users->map(function ($u) use ($from, $to) {

            $total = 0;

            $transactions = $u->transactions_made()
                ->whereDate('created_at', '>=', $from)
                ->whereDate('created_at', '<=', $to)
                ->where('state', true)
                ->get();

            foreach ($transactions as $transaction) {
                foreach ($transaction->dampings  as $damping) {
                    $total = $total + $damping->amount;
                }
            }

            return [
                'user' => [
                    "id" => $u->id,
                    "name" => $u->name,
                    "email" => $u->email,
                ],
                'amount' => $total
            ];
        });

        return response()->json($resp);
    }

    public function getVouchers(Request $request)
    {
        $data = $request->validate([
            'from' => 'required',
            'to' => 'required',
            'type' => 'required',
        ]);

        $vouchers = $this->doAndGetVouchers($data);

        return response()->json($vouchers);
    }

    public function getReports(Request $request)
    {

        $data = $request->validate([
            'from' => 'required',
            'to' => 'required',
        ]);

        $vouchers = $this->doAndGetVouchers($data);

        // $vouchers2 = $vouchers->toArray();

        // $vouchers = [];

        // foreach ($vouchers2 as $key => $value) {
        //     array_push($vouchers, $value);
        // }

        $totales = [
            VoucherType::getList()['R']['label'] => 0,
            VoucherType::getList()['B']['label'] => 0,
            VoucherType::getList()['F']['label'] => 0,
            'Gastos' => 0,
        ];


        foreach ($vouchers as $v) {
            if ($v['state']) {
                if ($v['voucher_type'] == 'R') {
                    $totales[VoucherType::getList()['R']['label']] = $totales[VoucherType::getList()['R']['label']] + $v['total'];
                }
                if ($v['voucher_type'] == 'B') {
                    $totales[VoucherType::getList()['B']['label']] = $totales[VoucherType::getList()['B']['label']] + $v['total'];
                }
                if ($v['voucher_type'] == 'F') {
                    $totales[VoucherType::getList()['F']['label']] = $totales[VoucherType::getList()['F']['label']] + $v['total'];
                }
            }
        }

        $spendings = Spending::query()
            ->where('date', '>=', date($data['from']))
            ->where('date', '<=', date($data['to']))
            ->get();

        foreach ($spendings as $s) {
            $totales['Gastos'] = $totales['Gastos'] + $s->amount;
        }

        $resp = [
            'vouchers' => $vouchers,
            'totales' =>  $totales,
        ];

        return response()->json($resp);
    }

    private function doAndGetVouchers($data)
    {
        $from = $data['from'];
        $to = $data['to'];
        $type = $data['type'] ?? null;
        $transactions = Transaction::query()
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->when($type, function ($query) use ($type) {
                return $query->where('voucher_type',  $type);
            })
            ->with('dampings.installment.payment.courseTurnStudent.student', 'dampings.installment.payment.sale.course_turn_student.student')
            ->get();

        $vouchers = $transactions->map(function ($t) {
            if (count($t->dampings) < 1) {
                return false;
            }
            if ($t->dampings[0]->installment->payment == null) {
                return false;
            }
            $studentName = '';
            if ($t->dampings[0]->installment->payment->courseTurnStudent) {
                $studentName = $t->dampings[0]->installment->payment->courseTurnStudent->student->name;
            }
            if ($t->dampings[0]->installment->payment->sale) {
                $studentName = $t->dampings[0]->installment->payment->sale->course_turn_student->student->name;
            }

            $total = 0;
            foreach ($t->dampings as $key => $value) {
                $total = $total + $value->amount;
            }

            return [
                'voucher' => $t->voucher,
                'voucher_type' => $t->voucher_type,
                'voucher_state' => VoucherState::getList()[$t->voucher_state]['label'] ?? null,
                'student' => $studentName,
                'date' => (new \Carbon\Carbon($t->created_at))->format('Y-m-d H:i:s'),
                'total' => $total,
                'responsable' => $t->responsable->name,
                'link' => $t->voucher_link,
                'state' => $t->state,
                'transaction_id' => $t->id,
            ];
        });

        $vouchers = $vouchers->filter(function ($e) {
            return $e;
        });

        $vouchers2 = $vouchers->toArray();

        $vouchers = [];

        foreach ($vouchers2 as $key => $value) {
            array_push($vouchers, $value);
        }

        return $vouchers;
    }

    public function getVoucherPdf($voucher)
    {
        $transaction = Transaction::query()
            ->with('detail')
            ->where('voucher', $voucher)
            ->firstOrFail();

        $payment = $transaction->dampings[0]->installment->payment;

        if ($payment->courseTurnStudent != null) {
            $student = $payment->courseTurnStudent->student;
        }
        if ($payment->sale != null) {
            $student = $payment->sale->course_turn_student->student;
        }

        $total = 0;
        foreach ($transaction->detail as $row) {
            $price = floatval($row['amount']);
            $total = $total + $price;
        }
        $formatter = new NumeroALetras();
        $total_text = $formatter->toInvoice($total);

        $data = [
            'voucher' => $voucher,
            'date' => $transaction->created_at->format('d/m/Y'),
            'client' => [
                'name' => $student->name,
                'dni' => $student->dni,
            ],
            'detail' => $transaction->detail->toArray(),
            'responsable' => $transaction->responsable->name,
            'total' => [
                'amount' => $total,
                // 'label' => 'SON: QUINCE Y 00/100 SOLES
                'label' => "SON: $total_text SOLES",
            ]
        ];

        $pdf = App::make('dompdf.wrapper');
        $pdf = PDF::loadView('exports.voucher', [
            'data' => $data
        ]);
        return $pdf->stream();
    }

    public function deletePay(Damping $damping)
    {
        $damping->state = false;
        $damping->save();

        $installment = $damping->installment;
        $installment->balance = $installment->balance + $damping->amount;
        $installment->save();

        return response()->json($damping);
    }
}
