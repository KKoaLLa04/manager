<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
