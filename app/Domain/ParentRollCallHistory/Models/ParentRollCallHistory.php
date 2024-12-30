<?php

namespace App\Domain\ParentRollCallHistory\Models;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\RollCall\Models\RollCall;
use App\Models\Classes;
use App\Models\ClassModel;
use App\Models\ClassSubjectTeacher;
use App\Models\DiemDanh;
use App\Models\Student;
use App\Models\TeacherSubjectTimetable;
use App\Models\Timetable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ParentRollCallHistory extends Model
{
    use HasFactory;

    public $table = "roll_call_history";

    protected $fillable = [
        'id',
        'student_id',
        'note',
        'class_id',
        'roll_call_id',
        'date',
        'time',
        'status',
        'is_deleted',
        'user_id',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Quan hệ với bảng classes
     */
    public function classes()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function teacherSubjectTimetable()
    {
        return $this->belongsTo(DiemDanh::class, 'teacher_subject_timetable_id', 'id');
    }
    public function rollCall()
    {
        return $this->belongsTo(RollCall::class, 'roll_call_id', 'id');
    }
    public function timetable()
    {
        return $this->hasOneThrough(
            Timetable::class,
            TeacherSubjectTimetable::class,
            'id',
            'id',
            'teacher_subject_timetable_id',
            'timetable_id'
        );
    }
}
