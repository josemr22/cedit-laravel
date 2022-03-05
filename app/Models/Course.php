<?php

namespace App\Models;

use App\Models\Turn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Course extends Model
{
    use HasFactory;

    public function turns()
    {
        return $this->belongsToMany(Turn::class)->withPivot('days', 'start_hour', 'end_hour');
    }
}
