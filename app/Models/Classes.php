<?php

namespace App\Models;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\AcademicYear\Models\AcademicYear;
use App\Domain\SchoolYear\Models\SchoolYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Classes extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'id',
        'name',
        'code',
        'school_year_id',
        'academic_year_id',
        'status',
        'grade_id',
        'is_deleted',
        'created_user_id',
        'modified_user_id',
        'created_at',
        'updated_at',
    ];

    public function grade(): HasOne
    {
        return $this->hasOne(Grade::class,'id','grade_id');
    }

    public function schoolYear(): HasOne
    {
        return $this->hasOne(SchoolYear::class,'id','school_year_id');
    }

    public function academicYear(): HasOne
    {
        return $this->hasOne(AcademicYear::class,'id','academic_year_id');
    }

    public function user(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_subject_teacher', 'class_id', 'user_id')
            ->wherePivot('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->wherePivot('access_type', StatusTeacherEnum::MAIN_TEACHER->value)
            ->withTimestamps()
            ->where('users.status', StatusEnum::ACTIVE->value);
    }


    public function subjectTeachers()
    {
        return $this->hasMany(ClassSubject::class, 'class_id');
    }

    public function teachers()
    {
        return $this->hasManyThrough(User::class, ClassSubjectTeacher::class, 'class_id', 'id', 'id', 'user_id');
    }

    public function studentClassHistories()
    {
        return $this->hasMany(StudentClassHistory::class, 'class_id', 'id');
    }
    

}
