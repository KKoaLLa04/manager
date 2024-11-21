<?php

namespace App\Domain\TeacherRollCallHistory\Models;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Models\Classes;
use App\Models\ClassSubjectTeacher;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class TeacherRollCallHistory extends Model
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
}
