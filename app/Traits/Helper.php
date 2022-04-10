<?php

namespace App\Traits;

use App\Models\Bank;
use App\Models\Correlative;
use App\Models\Transaction;
use Carbon\Carbon;

trait Helper
{
    public function createTransaction($transactionForm)
    {
        $voucher_type = $transactionForm['voucher_type'];
        $transaction = new Transaction();
        //TODO: CHANGE VOUCHER
        $transaction->voucher = 'ABCDEFG';
        $transaction->voucher_type = $transactionForm['voucher_type'];
        $transaction->voucher_state = 'E';
        $transaction->voucher_link = 'https://cixsolution.com';
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

        $correlativeNumber = Correlative::orderByDesc('code')->where('type', $voucher_type)->firstOrFail()->code;

        $newCorrelativeNumber = $correlativeNumber + 1;

        $newCorrelative = Correlative::create([
            'code' => $newCorrelativeNumber,
            'type' => $voucher_type
        ]);

        $now = (new \Carbon\Carbon())->format('d/m/Y');
        $code = $voucher_type . '001-' . str_pad($newCorrelativeNumber, '6', '7', STR_PAD_LEFT);

        $trama = "{$now}|{$code}|03|PEN|25.42|0.00|0.00|4.58|4.58|PEN||||||||0.00|2005|30.00||||||||||||0.00|||||||01|22:58:27||||||CONTADO||
CORPORACION CEDIT EIRL|CORPORACION CEDIT EIRL|20604594295|140101|AV. BALTA NRO. 424 INT. 203 (BALTA Y FRANCISCO CABRERA) |CHICLAYO|LAMBAYEQUE|CHICLAYO|PE CMOT8210|CMOTOS8210
42917981|1|GABRIEL CHANCAFE|SIMON CONDORI 286|PE|gabriel.chancafe.sistemas@gmail.com
TREINTA  CON 00/100 SOLES

1|UN|1.00|ARROZ CON PATO|30.00|01|4.58|4.58|10|1000|||||24|25.42|25.42|||18|0.00";

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

            $data = $soap->enviarCE('03', $trama);

            $ce = json_decode($data, true);

            $res = $ce['IND_OPERACION'];
            //echo "aaa".$res;
            //var_dump($ce);
            if ($res == '1') {
                // si paso exitoso en tu sistema ten un flag de envio a sunat y actualizalo a 1, para que sepan que
                // ya se envio a sunat
                $transaction->voucher_state = 'E';
                return [
                    'ok' => true,
                    'transaction' => $transaction,
                    'resp_sunat' => $ce,
                ];
            }
        } catch (Exception $e) {
            return [
                'ok' => false,
                'transaction' => $transaction,
                'resp_sunat' => $e->getMessage(),
            ];
        }
    }
}
