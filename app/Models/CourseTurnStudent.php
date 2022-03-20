<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseTurnStudent extends Model
{
    use HasFactory;

    protected $table = "course_turn_student";

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function courseTurn()
    {
        return $this->belongsTo(CourseTurn::class);
    }

    public function matriculator()
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'start_date' => 'datetime:Y-m-d',
    ];
}
