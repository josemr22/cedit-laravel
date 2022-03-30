<?php

namespace App\Http\Controllers;

use App\Models\Bank;
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
        // $banks = Bank::query()
        //     ->with('transactions')
        //     ->get();

        // $banks = Transaction::query()
        //     ->with('payment')
        //     ->with('damping')
        //     ->get()
        //     ->groupBy('bank_id');

        $banks = Bank::query()
            ->with('transactions.payment', 'transactions.dampings')
            // ->whereHas('category', function ($query) use ($from) {
            //     $query->where('name', 'like', "%{$search}%");
            // })
            ->get();

        $resp = $banks->map(function ($b) {

            $total = 0;

            foreach ($b->transactions as $transaction) {
                if ($transaction->payment != null) {
                    $total = $total + $transaction->payment->amount;
                } else {
                    foreach ($transaction->dampings  as $damping) {
                        $total = $total + $damping->amount;
                    }
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

        // return response()->json($banks);
        return response()->json($resp);
    }
}
