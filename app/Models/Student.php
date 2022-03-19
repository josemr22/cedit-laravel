<?php

namespace App\Models;

use App\Models\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function course_turn()
    {
        return $this->belongsTo(CourseTurn::class, 'course_turn_id');
    }

    public function registered_by()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
}
