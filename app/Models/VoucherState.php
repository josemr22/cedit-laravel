<?php

namespace App\Models;

class VoucherState
{
    public static function getList()
    {
        return [
            'E' => [
                'label' => 'Emitido',
            ],
            'F' => [
                'label' => 'Falló',
            ],
            'A' => [
                'label' => 'Anulado',
            ],
        ];
    }
}
