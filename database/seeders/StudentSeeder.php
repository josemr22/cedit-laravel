<?php

namespace Database\Seeders;

use App\Models\Damping;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Installment;
use App\Models\Transaction;
use Illuminate\Database\Seeder;
use App\Models\CourseTurnStudent;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Student::factory(50)->create();
        //Con un curso 
        $transaction1 = Transaction::create([
            'payment_date' => \Carbon\Carbon::now(),
            'bank_id' => 2,
            'user_id' => 1,
            'operation' => '123456789',
        ]);

        $transaction2 = Transaction::create([
            'payment_date' => \Carbon\Carbon::now(),
            'bank_id' => 2,
            'user_id' => 1,
            'operation' => '987654321',
        ]);

        $payment = new Payment();
        $payment->type = 1;
        $payment->amount = 1000;

        $payment->transaction_id = $transaction1->id;
        $payment->voucher = "ABCDEFGH";
        $payment->save();

        for ($i = 1; $i < 6; $i++) {
            CourseTurnStudent::create([
                'student_id' => $i,
                'course_turn_id' => $i,
                'enrolled_by' => 1,
                'payment_id' => 1,
                'start_date' => \Carbon\Carbon::now()
            ]);
        }

        //========================

        $payment = new Payment();
        $payment->type = 0;
        $payment->amount = 1000;
        $payment->save();

        //Datos de Matrícula
        $installment = new Installment();
        $installment->type = 'm';
        $installment->amount = 500;
        $installment->payment_id = $payment->id;
        $installment->save();

        $damping = new Damping();
        $damping->amount = 300;
        //TODO: CHANGE VOUCHER
        $damping->voucher = 'ABCDEFG';
        $damping->transaction_id = $transaction2->id;
        $damping->installment_id = $installment->id;
        $damping->save();
        $installment->balance = 200;
        $installment->save();
        //=================

        $installments = [
            [
                "amount" => 100,
                "pay" => 50
            ],
            [
                "amount" => 100
            ],
            [
                "amount" => 100
            ],
            [
                "amount" => 100
            ],
            [
                "amount" => 100
            ],
        ];
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
                //TODO: CHANGE VOUCHER
                $damping->voucher = 'ABCDEFG';
                $damping->transaction_id = $transaction2->id;
                $damping->installment_id = $installment->id;
                $damping->save();
                $installment->balance = floatval($item['amount']) - floatval($item['pay']);
            } else {
                $installment->balance = $item['amount'];
            }
            $installment->save();
            $firstInstallment = false;
        }

        CourseTurnStudent::create([
            'student_id' => 8,
            'course_turn_id' => 1,
            'enrolled_by' => 1,
            'payment_id' => $payment->id,
            'start_date' => \Carbon\Carbon::now()
        ]);

        // for ($i = 1; $i < 5; $i++) {
        //     $student = Student::factory(5)->create([
        //         'course_turn_id' => $i,
        //         'course_id' => $i,
        //         'signed_up' => 1,
        //         'enrolled_by' => 1,
        //         'enrolled_at' => \Carbon\Carbon::now(),
        //         'start_date' => \Carbon\Carbon::now(),
        //     ]);
        //     //Payment
        //     $payment = new Payment();

        //     $payment->type = 1;
        //     $payment->observation = "observation l";
        //     $payment->student_id = $student->id;
        //     $payment->amount = 500;
        //     $payment->save();
        //     if ($payment->type) {

        //         $payment->amount_payable = 500;
        //         $payment->save();

        //         $cuotas = 5;
        //         foreach ($cuotas as $item) {
        //             $cuota = new Fee();
        //             $cuota->amount = 100;
        //             $cuota->balance = 100;
        //             $cuota->payment_id = $payment->id;
        //             $payment->save();
        //         }
        //     }
        //     $transaction = new Transaction();
        //     $transaction->bank_id = 1;
        //     $transaction->operation = 'ABCDE12';
        //     $transaction->save();
        //     $payment->transaction_id = $transaction->id;
        // }
        // Student::factory(50)->create([
        //     'course_turn_id' => 1,
        //     'signed_up' => 1,
        //     'enrolled_by' => 1,
        //     'enrolled_at' => \Carbon\Carbon::now(),
        //     'start_date' => \Carbon\Carbon::now(),
        // ]);
    }
}
