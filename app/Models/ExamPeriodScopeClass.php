<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamPeriodScopeClass extends Model
{
    use HasFactory;
    protected $table = 'exam_period_scope_class';

    protected $fillable = [
        'id',
        'exam_period_id',
        'class_id',
        'is_deleted',
        'created_at',
        'updated_at',
    ];
}
