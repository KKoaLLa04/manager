<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    protected $table = 'timetable';
    protected $fillable = [
        "id",
        "day",
        "time",
        "from_time",
        "to_time",
        "person",
        "created_at",
        "updated_at",
    ];
}
