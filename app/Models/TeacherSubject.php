<?php

namespace App\Models;

use App\Domain\Subject\Models\Subject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherSubject extends Model
{
    use HasFactory;
    protected $table = 'teacher_subject';

    protected $fillable = [
        'subject_id',
        'user_id',
        'status',
        'is_deleted',
        'created_user_id',
        'modified_user_id',
    ];

    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}
// Trong model TeacherSubject.php
public function subject()
{
    return $this->belongsTo(Subject::class, 'subject_id' , 'id');
}


}
