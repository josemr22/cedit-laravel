<?php

namespace App\Models;

class Menu
{
    public static function getList()
    {
        return [
            'dashboard' => [
                'label' => 'Dashboard',
                'route' => '/dashboard',
            ],
            'users' => [
                'label' => 'Usuarios',
                'route' => '/usuarios',
            ],
            'students' => [
                'label' => 'Alumnos',
                'route' => '/alumnos/lista',
            ],
            'informs' => [
                'label' => 'Informes',
                'route' => '/alumnos/informes',
            ],
            'inscription' => [
                'label' => 'Inscripción',
                'route' => '/alumnos/inscripcion',
            ],
            'courses' => [
                'label' => 'Cursos',
                'route' => '/cursos',
            ],
            'till' => [
                'label' => 'Caja',
                'route' => '/caja/gastos',
            ],
            'uniforms' => [
                'label' => 'Uniformes',
                'route' => '/ventas/uniformes',
            ],
            'certificates' => [
                'label' => 'Certificados',
                'route' => '/ventas/certificados',
            ],
            'services' => [
                'label' => 'Servicios',
                'route' => '/ventas/servicios',
            ],
            'control' => [
                'label' => 'Control de Operación',
                'route' => '/caja/control-operacion',
            ]
        ];
    }
}
