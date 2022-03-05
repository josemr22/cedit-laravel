<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseTurn extends Model
{
    use HasFactory;

    protected $table = 'course_turn';

    public function turn()
    {
        return $this->belongsTo(Turn::class);
    }
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
