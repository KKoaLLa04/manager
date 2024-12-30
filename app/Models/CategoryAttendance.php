<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryAttendance extends Model
{
    protected $table = 'category_attendance';
    protected $fillable = [
        'id',
        'name',
        'from_date',
        'to_date',
        'is_deleted',
        'updated_at',
        'created_at',
    ];
}
