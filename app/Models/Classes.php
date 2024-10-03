<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'id',
        'name',
        'code',
        'main_teacher',
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

}
