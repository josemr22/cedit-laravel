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

        $p1 = Permission::create(['name' => 'dashboard']);
        $p2 = Permission::create(['name' => 'users']);
        $p3 = Permission::create(['name' => 'students']);
        $p4 = Permission::create(['name' => 'informs']);
        $p5 = Permission::create(['name' => 'inscription']);
        $p6 = Permission::create(['name' => 'courses']);
        $p7 = Permission::create(['name' => 'till']);
        $p8 = Permission::create(['name' => 'uniforms']);
        $p9 = Permission::create(['name' => 'certificates']);
        $p10 = Permission::create(['name' => 'services']);
        $p11 = Permission::create(['name' => 'control']);

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
            $p10,
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
            $p11,
        ]);
    }
}
