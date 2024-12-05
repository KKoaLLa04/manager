<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    protected $table = 'attendance_log';
    protected $fillable = [
        'id',
        'user_id',
        'class_id',
        'date',
        'is_deleted',
        'updated_at',
        'created_at',
    ];
}
