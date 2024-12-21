<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectTimetableConfig extends Model
{
    protected $table = 'subject_timetable_config';
    protected $fillable = [
        'id',
        'quantity',
        'subject_id',
        'is_deleted',
        'created_at',
        'updated_at',
    ];
}
