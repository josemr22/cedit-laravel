<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    public function courseTurnStudent()
    {
        return $this->hasOne(CourseTurnStudent::class);
    }

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];
}
