<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function course_turn_student()
    {
        return $this->belongsTo(CourseTurnStudent::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    protected $casts = [
        // 'created_at' => 'datetime:Y-m-d H:i:s',
    ];
}
