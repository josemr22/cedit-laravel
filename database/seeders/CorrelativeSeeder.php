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
            'code' => '247',
            'type' => 'R',
        ]);
        Correlative::create([
            'code' => '270',
            'type' => 'B',
        ]);
        Correlative::create([
            'code' => '247',
            'type' => 'F',
        ]);
    }
}
