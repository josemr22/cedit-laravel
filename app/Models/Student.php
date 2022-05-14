<?php

namespace App\Models;

use App\Models\Department;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes;

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

    public function coursesGroup()
    {
        return $this->belongsToMany(CourseTurn::class);
    }

    public function registered_by()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function course_turn_student()
    {
        return $this->hasMany(CourseTurnStudent::class);
    }

    // public function enrolled_by()
    // {
    //     return $this->belongsTo(User::class, 'registered_by');
    // }

    protected $casts = [
        'date_of_birth' => 'datetime:Y-m-d',
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];
}
