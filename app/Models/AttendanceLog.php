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
        'roll_call_id',
        'type',
        'teacher_subject_timetable_id',
        'is_deleted',
        'updated_at',
        'created_at',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
