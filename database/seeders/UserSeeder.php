<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        $user = User::factory()->create([
            'name' => 'Admin',
            'user' => 'Admin',
        ]);

        $user->assignRole('Administrador');
        
        $dueno = User::factory()->create([
            'name' => 'Dueño',
            'user' => 'Dueno',
        ]);

        $dueno->assignRole('Dueño');

        for ($i = 0; $i < 4; $i++) {
            $u = User::factory()->create();
            $u->assignRole('Ventas');
        }
    }
}
