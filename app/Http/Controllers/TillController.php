<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\User;
use App\Traits\Helper;
use App\Models\Damping;
use App\Models\Spending;
use App\Models\Installment;
use App\Models\Transaction;
use App\Models\VoucherType;
use App\Models\VoucherState;
use Illuminate\Http\Request;

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
        return response()->json($installment);
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

        $totales = [
            VoucherType::getList()['R']['label'] => 0,
            VoucherType::getList()['B']['label'] => 0,
            VoucherType::getList()['F']['label'] => 0,
            'Gastos' => 0,
        ];

        foreach ($vouchers as $v) {
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
            ->with('dampings.installment.payment.courseTurnStudent.student', 'dampings.installment.payment.sale.student')
            ->get();

        $vouchers = $transactions->map(function ($t) {
            $studentName = '';
            if ($t->dampings[0]->installment->payment->courseTurnStudent) {
                $studentName = $t->dampings[0]->installment->payment->courseTurnStudent->student->name;
            }
            if ($t->dampings[0]->installment->payment->sale) {
                $studentName = $t->dampings[0]->installment->payment->sale->student->name;
            }

            $total = 0;
            foreach ($t->dampings as $key => $value) {
                $total = $total + $value->amount;
            }

            return [
                'voucher' => $t->voucher,
                'voucher_type' => $t->voucher_type,
                'voucher_state' => VoucherState::getList()[$t->voucher_state]['label'],
                'student' => $studentName,
                'date' => (new \Carbon\Carbon($t->created_at))->format('Y-m-d H:i:s'),
                'total' => $total,
                'responsable' => $t->responsable->name,
                'link' => $t->voucher_link
            ];
        });

        return $vouchers;
    }
}
