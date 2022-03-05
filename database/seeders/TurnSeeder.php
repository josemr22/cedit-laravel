<?php

namespace Database\Seeders;

use App\Models\Turn;
use Illuminate\Database\Seeder;

class TurnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Turn::create([
            'name' => 'Mañana'
        ]);
        Turn::create([
            'name' => 'Tarde'
        ]);
        Turn::create([
            'name' => 'Noche'
        ]);
    }
}
