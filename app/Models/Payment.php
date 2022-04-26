<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    // public function transaction()
    // {
    //     return $this->belongsTo(Transaction::class);
    // }

    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    public function courseTurnStudent()
    {
        return $this->hasOne(CourseTurnStudent::class);
    }

    public function sale()
    {
        return $this->hasOne(Sale::class);
    }

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];
}
