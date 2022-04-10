<?php

namespace Database\Seeders;

use App\Models\Correlative;
use Illuminate\Database\Seeder;

class CorrelativeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Correlative::create([
            'code' => '220',
            'type' => 'B',
        ]);
    }
}
