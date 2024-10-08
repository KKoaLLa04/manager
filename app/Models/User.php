<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'fullname',
        'phone',
        'address',
        'code',
        'access_type',
        'dob',
        'status',
        'gender',
        'is_deleted',
        'created_user_id',
        'modified_user_id',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'user_student', 'user_id', 'student_id')
            ->wherePivot('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->withTimestamps()
            ->where('students.status', StatusEnum::ACTIVE->value);
        }
        
        
    public function assign_relationship(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'user_student', 'user_id', 'student_id')
            ->wherePivot('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('access_type', AccessTypeEnum::GUARDIAN->value) // Điều kiện chỉ lấy phụ huynh
            ->withTimestamps()
            ->where('students.status', StatusEnum::ACTIVE->value);
    }
        
    // public function assignParentToStudent(int $student_id, int $parent_id)
    // {
    //     // Kiểm tra phụ huynh có hợp lệ không (access_type = 3 là phụ huynh)
    //     $parent = self::where('id', $parent_id)
    //         ->where('access_type', AccessTypeEnum::GUARDIAN->value) // Kiểm tra nếu người dùng là phụ huynh
    //         ->where('is_deleted', 1) // Chỉ chọn phụ huynh đang hoạt động
    //         ->first();
    
    //     if (!$parent) {
    //         return ['message' => 'Phụ huynh không hợp lệ', 'status' => 'error'];
    //     }
    
    //     // Kiểm tra học sinh có tồn tại không
    //     $student = Student::find($student_id);
    //     if (!$student) {
    //         return ['message' => 'Học sinh không tồn tại', 'status' => 'error'];
    //     }
    
    //     // Gán phụ huynh cho học sinh
    //     $student->parents()->attach($parent->id, ['created_user_id' => auth()->user()->id]);
    
    //     return ['message' => 'Gán phụ huynh thành công cho học sinh ' . $student->fullname, 'status' => 'success'];
    // }
    

    
}
