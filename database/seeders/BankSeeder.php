<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Bank::create([
            'name' => 'PAGO EN EFECTIVO',
            'abbreviation' => '',
        ]);
        Bank::create([
            'name' => 'Banco de Crédito del Perú',
            'abbreviation' => 'BCP',
        ]);
        Bank::create([
            'name' => 'Banco de la Nación',
            'abbreviation' => 'BN',
        ]);
        Bank::create([
            'name' => 'Banco Internacional del Perú',
            'abbreviation' => 'Interbank',
        ]);
        Bank::create([
            'name' => 'BBVA Banco Continental',
            'abbreviation' => 'BBVA',
        ]);
        Bank::create([
            'name' => 'YAPE',
            'abbreviation' => 'YAPE',
        ]);
        Bank::create([
            'name' => 'Grupo Scotia',
            'abbreviation' => 'Scotiabank',
        ]);
    }
}
