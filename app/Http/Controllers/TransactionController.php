<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Transaction;
use App\Models\VoucherType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\Helper;

class TransactionController extends Controller
{
    //
    use Helper;

    public function show(Transaction $transaction)
    {
        $transaction = Transaction::where('id', $transaction->id)
            ->with('bank', 'responsable', 'dampings.installment.payment.sale', 'dampings.installment.payment.courseTurnStudent')
            ->firstOrFail();
        return response()->json($transaction);
    }

    public function update(Transaction $transaction, Request $request)
    {
        $data = $request->validate([
            'bank_id' => 'nullable',
            'operation' => 'nullable',
            'user_id' => 'required',
            'name' => 'nullable',
            'payment_date' => 'nullable',
        ]);
        $transaction->user_id = $data['user_id'];
        $transaction->bank_id = $data['bank_id'];
        $bank = Bank::findOrFail($data['bank_id']);
        if ($bank->id != 1 && $bank->id != 6) {
            $transaction->operation = $data['operation'];

            $transaction->name = null;
            $transaction->payment_date = null;
        }
        if ($bank->name == 'YAPE') {
            $transaction->name = $data['name'];
            $transaction->payment_date = new \Carbon\Carbon($data['payment_date']);

            $transaction->operation = null;
        }
        $transaction->save();
        return response()->json(true);
    }

    public function checkDelete(Transaction $transaction)
    {
        $transaction->state = false;
        $dampingsArr = $transaction->dampings;
        if (count($dampingsArr) > 1) {
            return response()->json(true);
        }
        return response()->json(false);
    }

    public function delete(Transaction $transaction)
    {
        if ($transaction->voucher_type != 'R') {
            $type = VoucherType::getList()[$transaction->voucher_type]['code'];
            $codeArr = explode("-", $transaction->voucher);
            $coddoc = $codeArr[0];
            $numdoc = $codeArr[1];

            $response = $this->cancelPayment($type, $coddoc, $numdoc);
        } else {
            $response = [
                'ok' => true,
                'message' => 'Anulado exitosamente'
            ];
        }

        if (!$response['ok']) {
            return response()->json($response);
        }

        $transaction = DB::transaction(function () use ($transaction) {
            $transaction->state = false;
            $transaction->voucher_state = 'A';
            $dampingsArr = $transaction->dampings;
            foreach ($dampingsArr as $damping) {
                $damping->state = false;
                $installment = $damping->installment;
                $installment->balance = $installment->balance + $damping->amount;
                $damping->save();
                $installment->save();
            }
            $transaction->save();
        });

        if ($transaction) {
            return response()->json([
                'ok' => false,
                'message' => 'Ocurrió un error en la base de datos'
            ]);
        }

        return response()->json($response);
    }

    public function resendToSunat(Request $request){
        $data = $request->validate([
            'voucher' => 'required'
        ]);

        $transaction = Transaction::where('voucher', $data['voucher'])->firstOrFail();

        if($transaction->voucher_type == 'F'){
            $studentArr = [
                'name' => strtoupper($transaction->razon_social),
                'address' => $transaction->address,
                'email' => $transaction->email,
                'num_doc' => $transaction->doc_num,
            ];
        }else{
            if($transaction->dampings[0]->installment->payment->courseTurnStudent != null){
                $student = $transaction->dampings[0]->installment->payment->courseTurnStudent->student;
            }else{
                $student = $transaction->dampings[0]->installment->payment->sale->course_turn_student->student;
            }

            $studentArr = [
                'name' => strtoupper($student->name),
                'address' => $student->address,
                'email' => $student->email,
                'num_doc' => $student->dni,
            ];
        }

        $pds = $transaction->detail;

        $pds = $pds->map(function($pd){
            return [
                'amount' => floatval($pd->amount),
                'label' => $pd->label
            ];
        })->toArray();

        $sunat_response = $this->sendToSunat($transaction->id, $studentArr, $payDetail);

        return response()->json($sunat_response);
    }
}
