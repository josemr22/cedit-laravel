<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Department::create([
            'name' => 'Amazonas'
        ]);
        Department::create([
            'name' => 'Áncash'
        ]);
        Department::create([
            'name' => 'Apurímac'
        ]);
        Department::create([
            'name' => 'Arequipa'
        ]);
        Department::create([
            'name' => 'Ayacucho'
        ]);
        Department::create([
            'name' => 'Cajamarca'
        ]);
        Department::create([
            'name' => 'Callao'
        ]);
        Department::create([
            'name' => 'Cusco'
        ]);
        Department::create([
            'name' => 'Huancavelica'
        ]);
        Department::create([
            'name' => 'Huánuco'
        ]);
        Department::create([
            'name' => 'Ica'
        ]);
        Department::create([
            'name' => 'Junín'
        ]);
        Department::create([
            'name' => 'La Libertad'
        ]);
        Department::create([
            'name' => 'Lambayeque'
        ]);
        Department::create([
            'name' => 'Lima'
        ]);
        Department::create([
            'name' => 'Loreto'
        ]);
        Department::create([
            'name' => 'Madre de Dios'
        ]);
        Department::create([
            'name' => 'Moquegua'
        ]);
        Department::create([
            'name' => 'Pasco'
        ]);
        Department::create([
            'name' => 'Piura'
        ]);
        Department::create([
            'name' => 'Puno'
        ]);
        Department::create([
            'name' => 'San Martín'
        ]);
        Department::create([
            'name' => 'Tacna'
        ]);
        Department::create([
            'name' => 'Tumbes'
        ]);
        Department::create([
            'name' => 'Ucayali'
        ]);
    }
}
