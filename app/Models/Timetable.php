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
        "period",
        "created_at",
        "updated_at",
    ];

    public function teacherSubjectTimetable()
    {
        return $this->hasMany(TeacherSubjectTimetable::class, 'timetable_id', 'id');
    }
    public function timetable()
    {
        return $this->belongsTo(Timetable::class, 'timetable_id', 'id');
    }

}
