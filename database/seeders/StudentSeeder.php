<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Student;
use Illuminate\Database\Seeder;

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
        // Student::factory(50)->create();
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
