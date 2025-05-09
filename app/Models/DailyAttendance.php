<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyAttendance extends Model
{
    protected $table = 'daily_attendance';
    protected $fillable = [
        'id',
        'user_id',
        'class_id',
        'date',
        'user_id',
        'subject_id',
        'is_deleted',
        'updated_at',
        'created_at',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
