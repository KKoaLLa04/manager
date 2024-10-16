<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'students';

    protected $fillable = [
        'email',
        'fullname',
        'address',
        'student_code',
        'dob',
        'status',
        'gender',
        'is_deleted',
        'created_user_id',
        'modified_user_id',
        'created_at',
        'updated_at',
    ];

    public function classHistories(){
        return $this->hasMany(StudentClassHistory::class, 'student_id', 'id');
    }
}
