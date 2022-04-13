<?php

namespace App\Models;

class SaleType
{
    public static function getList()
    {
        return [
            'u' => [
                'label' => 'Uniforme',
            ],
            'c' => [
                'label' => 'Certificado',
            ],
            's' => [
                'label' => 'Servicio',
            ],
        ];
    }
}
