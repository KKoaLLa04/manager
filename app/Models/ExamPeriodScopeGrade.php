<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamPeriodScopeGrade extends Model
{
    use HasFactory;
    protected $table = 'exam_period_scope_grade';

    protected $fillable = [
        'id',
        'exam_period_id',
        'grade_id',
        'is_deleted',
        'created_at',
        'updated_at',
    ];
}
