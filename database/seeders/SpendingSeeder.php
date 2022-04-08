<?php

namespace Database\Seeders;

use App\Models\Spending;
use Illuminate\Database\Seeder;

class SpendingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Spending::factory(10)->create();
    }
}
