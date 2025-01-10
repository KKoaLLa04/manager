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

    public $table = "roll_call";

    protected $fillable = ['student_id', 'date', 'time', 'note', 'status', 'class_id', 'is_deleted', 'created_user_id', 'modified_user_id', 'teacher_subject_timetable_id'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id', 'id');
    }


    public function attendanceBy()
    {
        return $this->belongsTo(User::class, 'created_user_id', 'id');
    }

    public function timetable()
    {
        return $this->belongsTo(DiemDanh::class, 'teacher_subject_timetable_id', 'id');
    }
    public function teacherSubjectTimetable()
    {
        return $this->belongsTo(TeacherSubjectTimetable::class, 'teacher_subject_timetable_id', 'id');
    }
    public function createdUser()
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }

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

    public function teacherSubject(){
        return $this->hasOneThrough(
            User::class,
            ClassSubjectTeacher::class,
            'class_id', // Khóa ngoại trong bảng class_subject_teacher
            'id', // Khóa chính trong bảng users
            'class_id', // Khóa chính trong bảng RollCallHistory
            'user_id' // Khóa chính trong bảng class_subject_teacher
        )->where('class_subject_teacher.is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('class_subject_teacher.access_type', StatusTeacherEnum::TEACHER->value)
            ->where('class_subject_teacher.status', StatusEnum::ACTIVE->value);
    }
}
