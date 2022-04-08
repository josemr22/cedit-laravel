<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\User;
use App\Models\Damping;
use App\Models\Installment;
use App\Models\Transaction;
use Illuminate\Http\Request;

class TillController extends Controller
{
    //
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
        return response()->json($installment);
    }

    // public function getVouchers(){
    //     $voucher_type = request('voucher_type') ?? '';
    //     $from = request('from');
    //     $to = request('to');
    // }

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
}
