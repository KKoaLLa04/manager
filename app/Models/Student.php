<?php

namespace App\Models;

use App\Common\Enums\DeleteEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;
    public $table = 'students';

    protected $fillable = [       
        'fullname','address','student_code','dob','status','gender','is_deleted','created_user_id','modified_user_id','created_at','updated_at',
    ];
    public function classHistory()
    {
        return $this->hasOne(StudentClassHistory::class, 'student_id')->where('is_deleted', DeleteEnum::DELETED->value);

    }
}
