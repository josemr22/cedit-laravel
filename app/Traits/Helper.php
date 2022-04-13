<?php

namespace App\Traits;

use App\Models\Bank;
use App\Models\Correlative;
use App\Models\Transaction;
use App\Models\VoucherType;
use Carbon\Carbon;

trait Helper
{
    public function createTransaction($transactionForm)
    {
        $transaction = new Transaction();
        $transaction->voucher_type = $transactionForm['voucher_type'];
        $transaction->voucher_link = 'https://cixsolution.com';
        $transaction->user_id = $transactionForm['user_id'];
        $transaction->bank_id = $transactionForm['bank_id'];
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

    public function sendToSunat($transactionId, $student, $payDetail)
    {
        $transaction = Transaction::findOrFail($transactionId);
        $voucher_type = $transaction->voucher_type;

        $now = new \Carbon\Carbon();
        $nowDate = (new \Carbon\Carbon($now))->format('d/m/Y');
        $nowHour = (new \Carbon\Carbon($now))->format('H:i:s');
        $doc_type = VoucherType::getList()[$voucher_type]['code'];

        $correlativeNumber = Correlative::orderByDesc('code')->where('type', $voucher_type)->firstOrFail()->code;
        $newCorrelativeNumber = $correlativeNumber + 1;
        Correlative::create([
            'code' => $newCorrelativeNumber,
            'type' => $voucher_type
        ]);
        $code = $voucher_type . '001-' . str_pad($newCorrelativeNumber, 7, '0', STR_PAD_LEFT);
        $transaction->voucher = $code;
        $detail = $this->getPaymentData($payDetail)['detail_string'];
        $total = $this->getPaymentData($payDetail)['total'];
        $total_text = $this->getPaymentData($payDetail)['total_text'];
        $total_tax = $this->getPaymentData($payDetail)['total_tax'];
        $total_without_tax = $this->getPaymentData($payDetail)['total_without_tax'];

        $trama = "{$nowDate}|{$code}|{$doc_type}|PEN|{$total_without_tax}|0.00|0.00|{$total_tax}|{$total_tax}|PEN||||||||0.00|2005|{$total}||||||||||||0.00|||||||01|{$nowHour}||||||CONTADO||
CORPORACION CEDIT EIRL|CORPORACION CEDIT EIRL|20604594295|140101|AV. BALTA NRO. 424 INT. 203 (BALTA Y FRANCISCO CABRERA) |CHICLAYO|LAMBAYEQUE|CHICLAYO|PE CMOT8210|CMOTOS8210
42917981|1|{$student['name']}|{$student['address']}|PE|{$student['email']}
$total_text

$detail";

        $wsdl = "http://wscedit.cixsolution.com/dcaf84158950748f2ece0bf596df73a6/20604594295?wsdl";

        $options = array(
            'uri' => 'http://schemas.xmlsoap.org/soap/envelope/',
            'style' => 1,
            'use' => 1,
            'soap_version' => 1,
            'cache_wsdl' => 0,
            'connection_timeout' => 15,
            'trace' => true,
            'encoding' => 'UTF-8',
            'exceptions' => true,
        );

        try {
            $soap = new \SoapClient($wsdl, $options);

            $data = $soap->enviarCE($doc_type, $trama);

            $ce = json_decode($data, true);

            $res = $ce['IND_OPERACION'];
            if ($res == '1') {
                $transaction->voucher_state = 'E';
            } else {
                $transaction->voucher_state = 'R';
            }
            $transaction->save();
            return  $ce;
        } catch (Exception $e) {
            $transaction->voucher_state = 'F';
            $transaction->save();
            return $e->getMessage();
        }
    }

    private function getPaymentData($detail)
    {
        $detailString = '';
        $total = 0;
        foreach ($detail as $key => $row) {
            $price = floatval($row['amount']);
            $total = $total + $price;

            $tax = $price - ($price / 1.18);
            $price_without_tax = $price - $tax;

            $price = number_format($price, 2);
            $tax = number_format($tax, 2);
            $price_without_tax = number_format($price_without_tax, 2);

            $rowNumber = $key + 1;
            $rowString = "$rowNumber|UN|1.00|{$row['label']}|$price|01|$tax|$tax|10|1000|||||24|$price_without_tax|$price_without_tax|||18|0.00\r\n";
            $detailString = $detailString . $rowString;
        }
        $total_tax = $total - ($total / 1.18);
        $total_without_tax = $total - $total_tax;
        return [
            'detail_string' => $detailString,
            'total' => number_format($total, 2),
            'total_tax' => number_format($total_tax, 2),
            'total_without_tax' => number_format($total_without_tax, 2),
            'total_text' => 'SESENTA CON 00/100 SOLES'
        ];
    }
}
