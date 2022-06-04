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
                'title' => 'Factura Electrónica'
            ],
            'R' => [
                'label' => 'Recibo',
                'code' => '07',
                'title' => 'Recibo Electrónico'
            ],
            'B' => [
                'label' => 'Boleta',
                'code' => '03',
                'title' => 'Boleta Electrónica'
            ],
        ];
    }
}
