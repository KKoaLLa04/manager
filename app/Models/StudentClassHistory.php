<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentClassHistory extends Model
{
    protected $table = 'student_class_history';

    protected $fillable = [
        'student_id',
        'class_id',
        'start_date',
        'end_date',
        'status',
        'is_deleted',
        'created_by',
        'updated_by',
        'created_user_id',
        'modified_user_id',
        'id'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    // Khóa ngoại tới bảng classes
    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id', 'id');
    }

}
