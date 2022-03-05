<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        $administrador = Role::create(['name' => 'Administrador']);

        $ventas = Role::create(['name' => 'Ventas']);

        $tesoreria = Role::create(['name' => 'Tesorería']);

        $marketing = Role::create(['name' => 'Marketing']);

        $imagen = Role::create(['name' => 'Imagen']);

        $profesor = Role::create(['name' => 'Profesor']);

        $asistencia = Role::create(['name' => 'Asistencia']);

        $dueño = Role::create(['name' => 'Dueño']);

        $p1 = Permission::create(['name' => '1']);
        $p2 = Permission::create(['name' => '2']);
        $p3 = Permission::create(['name' => '3']);
        $p4 = Permission::create(['name' => '4']);
        $p5 = Permission::create(['name' => '5']);
        $p6 = Permission::create(['name' => '6']);
        $p7 = Permission::create(['name' => '7']);
        $p8 = Permission::create(['name' => '8']);
        $p9 = Permission::create(['name' => '9']);
        $p10 = Permission::create(['name' => '10']);

        $administrador->syncPermissions([
            $p1,
            $p2,
            $p3,
            $p4,
            $p5,
            $p6,
            $p7,
            $p8,
            $p9,
        ]);
        $ventas->syncPermissions([
            $p3,
            $p4,
            $p5,
            $p7,
            $p8,
            $p9,
            $p10,
        ]);
        $tesoreria->syncPermissions([
            $p7,
            $p2,
        ]);
        $marketing->syncPermissions([
            $p1,
        ]);
        $imagen->syncPermissions([
            $p3,
        ]);
        $profesor->syncPermissions([]);
        $asistencia->syncPermissions([]);
        $dueño->syncPermissions([
            $p1,
            $p2,
            $p3,
            $p4,
            $p5,
            $p6,
            $p7,
            $p8,
            $p9,
            $p10,
        ]);
    }
}
