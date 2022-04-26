<?php

namespace App\Models;

class VoucherType
{
    public static function getList()
    {
        return [
            'F' => [
                'label' => 'Factura',
                'code' => '01',
            ],
            'R' => [
                'label' => 'Recibo',
                'code' => '07',
            ],
            'B' => [
                'label' => 'Boleta',
                'code' => '03',
            ],
        ];
    }
}
