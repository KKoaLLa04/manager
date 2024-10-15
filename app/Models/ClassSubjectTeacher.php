<?php

namespace App\Models;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusTeacherEnum;
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


}
