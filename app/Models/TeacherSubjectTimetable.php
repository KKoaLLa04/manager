<?php

namespace App\Models;

use App\Domain\RollCall\Models\RollCall;
use Illuminate\Database\Eloquent\Model;

class TeacherSubjectTimetable extends Model
{
    protected $table = 'teacher_subject_timetable';
    protected $fillable = [
        "id",
        "class_subject_teacher_id",
        "timetable_id",
        "class_id",
        "is_deleted",
        "created_at",
        "updated_at",
    ];

    public function classSubjectTeacher()
    {
        return $this->hasOne(ClassSubjectTeacher::class, 'id', 'class_subject_teacher_id');
    }

    public function class()
    {
        return $this->hasOne(Classes::class, 'id', 'class_id');
    }
    
    public function rollcalls()
    {
        return $this->hasMany(RollCall::class,'teacher_subject_timetable_id','id');
    }

    public function timetable()
    {
        return $this->hasOne(Timetable::class, 'id', 'timetable_id');
    }

}
