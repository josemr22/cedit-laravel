<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function damping()
    {
        return $this->hasOne(Damping::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // protected $casts = [
    //     'created_at' => 'datetime:Y-m-d H:i:s',
    // ];
}