<?php

namespace App\Traits;

use App\Models\Bank;
use App\Models\Transaction;

trait Helper
{
    public function createTransaction($transactionForm)
    {

        $trama = "06/06/2018|B001-00000001|03|USD|338.98|0.00|0.00|61.02|61.02|USD||||||||0.00|2005|400.00||||||||||||0.00|||||||01
        GRUPPO SEPARIN S.R.L.||20536435078||CAL. LOS ABETOS NRO. 660 URB. ROSALES DE SALAMANCA  LIMA - LIMA - ATE|||||PE|CMOT8210|CMOTOS8210
        40757308|1|WILLIAMS  CESAR   GARRIAZO  LOBO|JR. CASMA 267 MIRAMARBAJO|PE|will65_6@hotmail.com
        CUATROCIENTOS Y 00/100 DOLARES AMERICANOS
        
        1|UN|1|KIT COMBO SEC BORA 32 APACHE+TQ600X200 BI|400.00|01|61.02|61.02|10|1000|||||00000000022|338.98|338.98|||18.00|0.00
        2|UN|1|TANQUE SEPAGAS  600 X 200  9.0 GLNS  BI TOROIDAL|0.00|01|0.00|0.00|10|1000|||||00000000278|0.00|0.00|||18.00|0.00
        3|UN|1|KIT SECUENCIAL 4 CIL BORA 32 -  ZAVOLI 200 - 30 APACHE|0.00|01|0.00|0.00|10|1000|||||00000000155|0.00|0.00|||18.00|0.00
        4|UN|4|GIGLER 1.75 MM|0.00|01|0.00|0.00|10|1000|||||00000002108|0.00|0.00|||18.00|0.00";

        $wsdl = 'http://wscedit.cixsolution.com/50e3c1aff9cde504a9faf9849753ff9c/20606543027?wsdl';

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
            $data = $soap->enviarCE('DNI', $trama);
            $ce = json_decode($data, true);
            $res = $ce['IND_OPERACION'];
            //echo "aaa".$res;
            //var_dump($ce);
            return response()->json($res);
            if ($res == '1') {
                // si paso exitoso en tu sistema ten un flag de envio a sunat y actualizalo a 1, para que sepan que
                // ya se envio a sunat

            }
        } catch (Exception $e) {
            return response()->json($e);
            die($e->getMessage());
        }


        // ===========================
        $transaction = new Transaction();
        //TODO: CHANGE VOUCHER
        $transaction->voucher = 'ABCDEFG';
        $transaction->voucher_type = 'R';
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
        return $transaction;
    }
}
