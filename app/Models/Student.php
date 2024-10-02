<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    public $table = 'students';

    protected $fillable = [       
        'fullname','address','student_code','dob','status','gender','is_deleted','created_user_id','modified_user_id','created_at','updated_at',
    ];
}
