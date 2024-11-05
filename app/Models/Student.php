<?php

namespace App\Models;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Domain\SchoolYear\Models\SchoolYear;
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
        return $this->hasMany(StudentClassHistory::class, 'student_id')->where('is_deleted', DeleteEnum::NOT_DELETE->value);
    }
    
    public function parents()
    {
        return $this->belongsToMany(User::class, 'user_student', 'student_id', 'user_id')
                    ->where('access_type', AccessTypeEnum::GUARDIAN->value) // Chỉ lấy user có access_type là phụ huynh
                    ->where('users.is_deleted', DeleteEnum::NOT_DELETE->value) // Chỉ lấy user chưa bị xóa
                    ->select('users.id', 'fullname', 'phone', 'code', 'gender', 'email', 'dob') // Chỉ lấy các trường cần thiết
                    ->withPivot([]); // Không lấy thông tin pivot
    }
    
    public function schoolYear()
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_name'); // Thay 'school_year_id' bằng tên trường thực tế trong bảng student
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($student) {
            // Tạo mã student_code tự động
            $student->student_code = Student::generateStudentCode();
        });
    }

    // Hàm sinh mã student_code
    public static function generateStudentCode()
    {
        // Ví dụ sinh mã với format: STUD-xxx (3 số ngẫu nhiên)
        return 'STU-' . str_pad(rand(0, 999), 4, '0', STR_PAD_LEFT);
    }


    


    public function classHistories(){
        return $this->hasMany(StudentClassHistory::class, 'student_id', 'id');
    }
}
