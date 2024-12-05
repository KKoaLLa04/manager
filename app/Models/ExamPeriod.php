<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamPeriod extends Model
{
    use HasFactory;
    protected $table = 'exam_period';


    protected $fillable = [
        'id',
        'exam_id',
        'date',
        'created_by',
        'updated_by',
        'is_deleted',
        'created_at',
        'updated_at',
    ];


}
