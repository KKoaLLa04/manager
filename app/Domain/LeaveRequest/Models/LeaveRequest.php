<?php

namespace App\Domain\LeaveRequest\Models;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Models\Classes;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class LeaveRequest extends Model
{
    use HasFactory;

    public $table = "leave_request";

    protected $fillable = ['code','title','note','leave_date','return_date','time','status','processed_by','student_id','parent_user_id','class_id','refuse_note','is_deleted','created_at','updated_at'];

    public function student(){
        return $this->belongsTo(Student::class,'student_id')->where('is_deleted',DeleteEnum::NOT_DELETE->value)->where('status',StatusEnum::ACTIVE->value);
    }

    public function class(){
        return $this->belongsTo(Classes::class,'class_id')->where('is_deleted',DeleteEnum::NOT_DELETE->value)->where('status',StatusEnum::ACTIVE->value);
    }

    public function parent(){
        return $this->belongsTo(User::class,'parent_user_id')->where('access_type',AccessTypeEnum::GUARDIAN->value)->where('status',StatusEnum::ACTIVE->value);
    }

    public function processedBy(){
        return $this->belongsTo(User::class,'processed_by')->where('is_deleted',DeleteEnum::NOT_DELETE->value)->where('status',StatusEnum::ACTIVE->value);
    }
}
