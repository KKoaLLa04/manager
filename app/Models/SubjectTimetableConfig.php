<?php

namespace App\Models;

use App\Domain\Subject\Models\Subject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
