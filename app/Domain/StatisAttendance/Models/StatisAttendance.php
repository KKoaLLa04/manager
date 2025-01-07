<?php

namespace App\Domain\StatisAttendance\Models;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\RollCall\Models\RollCall;
use App\Models\Classes;
use App\Models\ClassSubjectTeacher;
use App\Models\DiemDanh;
use App\Models\Student;
use App\Models\TeacherSubjectTimetable;
use App\Models\Timetable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class StatisAttendance extends Model
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

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id');
    }

    // public function mainTeacher()
    // {
    //     return $this->hasOne(ClassSubjectTeacher::class, 'class_id')
    //         ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
    //         ->where('access_type', StatusTeacherEnum::MAIN_TEACHER->value)
    //         ->where('status', StatusEnum::ACTIVE->value)
    //         ->with('user'); // Tải thông tin người dùng
    // }

    // Trong mô hình RollCallHistory
    public function mainTeacher()
    {
        return $this->hasOneThrough(
            User::class,
            ClassSubjectTeacher::class,
            'class_id', // Khóa ngoại trong bảng class_subject_teacher
            'id', // Khóa chính trong bảng users
            'class_id', // Khóa chính trong bảng RollCallHistory
            'user_id' // Khóa chính trong bảng class_subject_teacher
        )->where('class_subject_teacher.is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('class_subject_teacher.access_type', StatusTeacherEnum::MAIN_TEACHER->value)
            ->where('class_subject_teacher.status', StatusEnum::ACTIVE->value);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function student()
    {
        // Giả sử khoá ngoại là `student_id`
        return $this->belongsTo(Student::class, 'student_id', 'id');
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
    public function diemdanh()
    {
        return $this->hasMany(DiemDanh::class, 'class_id', 'class_id');
    }
}
