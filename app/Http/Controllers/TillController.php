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
}
