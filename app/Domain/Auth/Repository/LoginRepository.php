<?php

namespace App\Domain\Auth\Repository;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class LoginRepository
{
    public function __construct()
    {
    }

    public function checkLogin($username): ?User
    {
        return User::where('username', $username)
            ->with(['students'])
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)->first();

    }

    public function getStudentOfUser(?User $user): array
    {
        if ($user->access_type == AccessTypeEnum::GUARDIAN->value && $user->students->isNotEmpty()) {
            $students = [];
            foreach ($user->students as $student) {
                $students[] = [
                    'id'           => $student->id,
                    'student_code' => !is_null($student->student_code) ? $student->student_code : "",
                    'fullname'     => !is_null($student->fullname) ? $student->fullname : "",
                    'dob'          => !is_null($student->dob) ? Carbon::parse($student->dob)->timestamp : "",
                    'gender'       => $student->gender,
                    'address'      => !is_null($student->address) ? $student->address : "",
                ];
            }
            return $students;
        }
        return [];
    }

    public function transform(?User $user, array $studentOfUser, string $token): array
    {
        return [
            'token' => $token,
            "user"  => [
                'id'          => $user->id,
                'code'        => !is_null($user->code) ? $user->code : "",
                'fullname'    => !is_null($user->fullname) ? $user->fullname : "",
                'phone'       => !is_null($user->phone) ? $user->phone : "",
                'access_type' => $user->access_type,
                'dob'         => !is_null($user->dob) ? Carbon::parse($user->dob)->timestamp : "",
                'gender'      => $user->gender,
                'address'     => !is_null($user->address) ? $user->address : "",
                'email'       => !is_null($user->email) ? $user->email : "",
                'username'    => !is_null($user->username) ? $user->username : "",
                'students'    => $studentOfUser
            ]
        ];
    }
}