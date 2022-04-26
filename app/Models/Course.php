<?php

namespace App\Models;

use App\Models\Turn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    public function turns()
    {
        return $this->belongsToMany(Turn::class)->withPivot('days', 'start_hour', 'end_hour');
    }
}
