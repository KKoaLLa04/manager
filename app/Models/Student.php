<?php

namespace App\Models;

use App\Common\Enums\AccessTypeEnum;
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
        return $this->hasOne(StudentClassHistory::class, 'student_id')->where('is_deleted', DeleteEnum::NOT_DELETE->value);

    }
    public function parents()
    {
        return $this->belongsToMany(User::class, 'user_student', 'student_id', 'user_id')
                    ->where('access_type', AccessTypeEnum::GUARDIAN->value); // Chỉ lấy user có access_type là phụ huynh
    }
    
}
