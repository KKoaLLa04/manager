<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserStudent extends Model
{
    use HasFactory;

    public $table = 'user_student';

    protected $fillable = [       
        'user_id','student_id','is_deleted','created_user_id','modified_user_id','created_at','updated_at',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
