<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Damping extends Model
{
    use HasFactory, SoftDeletes;

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function installment()
    {
        return $this->belongsTo(Installment::class);
    }

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];
}
