<?php

namespace App\Models;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\Subject\Models\Subject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClassSubjectTeacher extends Model
{

    protected $table = 'class_subject_teacher';
    protected $fillable = [
        'class_subject_id',
        'class_id',
        'user_id',
        'start_date',
        'end_date',
        'status',
        'access_type',
        'is_deleted',
        'created_user_id',
        'modified_user_id',
        'created_at',
        'updated_at'
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id')
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('access_type', StatusTeacherEnum::MAIN_TEACHER->value)
            ->where('status', StatusEnum::ACTIVE->value);
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id')
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('status', StatusEnum::ACTIVE->value);
    }


    public function class()
    {
        return $this->hasOne(Classes::class, 'class_id', 'id');
    }
    
    //tai khoan
    public function subject()
{
    return $this->hasOneThrough(
        Subject::class,             // Bảng cuối cùng là Subject
        ClassSubject::class,        // Bảng trung gian là ClassSubject
        'id',                       // Khóa ngoại của ClassSubject trỏ đến ClassSubjectTeacher
        'id',                       // Khóa chính của Subject
        'class_subject_id',         // Khóa ngoại của ClassSubjectTeacher trỏ tới ClassSubject
        'subject_id'                // Khóa ngoại của ClassSubject trỏ tới Subject
    )->where('class_subject.is_deleted', DeleteEnum::NOT_DELETE->value)
     ->where('subjects.is_deleted', DeleteEnum::NOT_DELETE->value);
}
    
    public function classSubject(): HasOne
    {
        return $this->hasOne(ClassSubject::class, 'id', 'class_subject_id')
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value);
    }
}
