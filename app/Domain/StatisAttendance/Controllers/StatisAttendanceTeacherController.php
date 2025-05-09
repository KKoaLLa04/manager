<?php

namespace App\Domain\StatisAttendance\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\StatisAttendance\Repository\StatisAttendanceTeacherResponsitory;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatisAttendanceTeacherController extends BaseController
{

    protected $statisAttendance;
    public function __construct(StatisAttendanceTeacherResponsitory $statisAttendanceResponsitory)
    {
        $this->statisAttendance = $statisAttendanceResponsitory;
    }
    public function index(GetUserRepository $getUserRepository)
    {
        // Lấy thông tin giáo viên đang đăng nhập
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::TEACHER->value;

        // Kiểm tra quyền truy cập của người dùng
        $showUser = $getUserRepository->getUser($user_id, $type);
        if (!$showUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        // Gọi hàm lấy tổng số học sinh đã điểm danh
        $totalAttendance = $this->statisAttendance->getAllAttendanceTeachersOnDay($user_id);

        // Trả về kết quả
        if ($totalAttendance) {
            return $this->responseSuccess($totalAttendance, trans('api.StatisAttendance.showShoolOnDayWithClass.success'));
        } else {
            return $this->responseError(trans('api.StatisAttendance.showShoolOnDayWithClass.errors'));
        }
    }

    public function getAllAttendanceOnWeek(GetUserRepository $getUserRepository)
    {
        // Lấy thông tin giáo viên đang đăng nhập
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::TEACHER->value;

        // Kiểm tra quyền truy cập của người dùng
        $showUser = $getUserRepository->getUser($user_id, $type);
        if (!$showUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        // Gọi hàm lấy tổng số học sinh đã điểm danh
        $totalAttendance = $this->statisAttendance->getAllAttendanceTeachersOnWeek($user_id);

        // Trả về kết quả
        if ($totalAttendance) {
            return $this->responseSuccess($totalAttendance, trans('api.StatisAttendance.showShoolOnWeekWithClass.success'));
        } else {
            return $this->responseError(trans('api.StatisAttendance.showShoolOnWeekWithClass.errors'));
        }
    }

    public function getAllAttendanceOnMonth(GetUserRepository $getUserRepository){
        // Lấy thông tin giáo viên đang đăng nhập
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::TEACHER->value;

        // Kiểm tra quyền truy cập của người dùng
        $showUser = $getUserRepository->getUser($user_id, $type);
        if (!$showUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        // Gọi hàm lấy tổng số học sinh đã điểm danh
        $totalAttendance = $this->statisAttendance->getAllAttendanceTeachersOnMonth($user_id);

        // Trả về kết quả
        if ($totalAttendance) {
            return $this->responseSuccess($totalAttendance, trans('api.StatisAttendance.showShoolOnMonthWithClass.success'));
        } else {
            return $this->responseError(trans('api.StatisAttendance.showShoolOnMonthWithClass.errors'));
        }
    }
}
