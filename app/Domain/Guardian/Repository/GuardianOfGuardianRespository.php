<?php

namespace App\Domain\Guardian\Repository;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Domain\Guardian\Models\Guardian;
use App\Models\Student;
use Exception;
use Illuminate\Support\Facades\Auth;

class GuardianOfGuardianRespository
{
    public function __construct() {}

    public function getOneGuardian()
    {
        // Lấy thông tin người dùng hiện tại
        $user = Auth::user();

        // Kiểm tra nếu không có người dùng đăng nhập
        if (!$user) {
            return [
                'msg' => 'Người dùng không đăng nhập',
                'data' => null,
            ];
        }

        // Trả về tên và email của người dùng
        return [
            'data' => [
                'fullname' => $user->fullname,
                'email' => $user->email,
                'phone' => $user->phone,
                'dob' => strtotime($user->dob),
                'gender' => $user->gender,
                'career' => $user->career,
                'status' => $user->status,
                'address' => $user->address,
                'username' => $user->username,
            ],
        ];
    }

    public function getStudentInGuardian()
{
    // Lấy thông tin người dùng hiện tại (phụ huynh)
    $user = Auth::user();

    // Kiểm tra nếu không có người dùng đăng nhập
    if (!$user) {
        return [
            'msg' => 'Người dùng không đăng nhập',
            'data' => null,
        ];
    }

    // Lấy học sinh của phụ huynh thông qua trường guardian_id
    $students = $user->students;  // Lấy danh sách học sinh liên kết với phụ huynh qua trường guardian_id

    // Kiểm tra nếu không có học sinh nào
    if ($students->isEmpty()) {
        return [
            'msg' => 'Không có học sinh nào liên kết với phụ huynh',
            'data' => null,
        ];
    }

    // Trả về danh sách học sinh
    return [
        'msg' => 'Danh sách học sinh của phụ huynh',
        'data' => $students,
    ];
}


}
