<?php

namespace App\Models;

class VoucherType
{
    public static function getList()
    {
        return [
            'R' => [
                'label' => 'Recibo',
            ],
            'B' => [
                'label' => 'Boleta',
            ],
            'F' => [
                'label' => 'Factura',
            ],
        ];
    }
}
