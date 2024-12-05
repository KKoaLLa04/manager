<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PointStudent extends Model
{
    use HasFactory;
    protected $table = 'point_student';

    protected $fillable = [
        'id',
        'student_id',
        'exam_period_id',
        'class_id',
        'point',
        'date',
        'created_by',
        'updated_by',
        'is_deleted',
        'created_at',
        'updated_at',
    ];
}
