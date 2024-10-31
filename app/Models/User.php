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
use App\Common\Enums\StatusTeacherEnum;
use App\Models\Classes;
use Illuminate\Database\Eloquent\Relations\HasMany;

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


    public function infoMainTearchWithClass () {

        $itemTearchMainHasClass = ClassSubjectTeacher::where('user_id', $this->id)->where('end_date', null)->where('access_type', StatusTeacherEnum::MAIN_TEACHER->value)->where('status', StatusEnum::ACTIVE->value)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->first();

        if($itemTearchMainHasClass){

            $class = Classes::find($itemTearchMainHasClass->class_id);

            // return array_merge(
            //     $this->toArray(),
            //     ['class' => $class->toArray()]
            // );

            return [
                "userId" => $this->id,
                "userName" => $this->fullname,
                "userCode" => $this->code,
                "userEmail" => $this->email,
                "userPhone" => $this->phone,
                "userMainClassName" => $class->name,
                "userMainClassId" => $class->id,
                "userAccessType" => $this->access_type,
                "userStatus" => $this->status,
                "userDob" => strtotime($this->dob),
            ];

        }else{

            // return array_merge(
            //     $this->toArray(),
            //     ['class' => []]
            // );

            return [
                "userId" => $this->id,
                "userName" => $this->fullname,
                "userCode" => $this->code,
                "userEmail" => $this->email,
                "userPhone" => $this->phone,
                "userMainClassName" => "",
                "userMainClassId" => "",
                "userAccessType" => $this->access_type,
                "userStatus" => $this->status,
                "userDob" => strtotime($this->dob),
            ];

        }

    }
//tai khoan
    public function classSubjectTeacher(): HasMany
    {
        return $this->hasMany(ClassSubjectTeacher::class, 'user_id', 'id');
    }
 

    }



