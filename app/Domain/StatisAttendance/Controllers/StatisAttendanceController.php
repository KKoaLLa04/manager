<?php

namespace App\Domain\StatisAttendance\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\StatisAttendance\Repository\StatisAttendanceResponsitory;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatisAttendanceController extends BaseController
{

    protected $statisAttendance;
    public function __construct(StatisAttendanceResponsitory $statisAttendanceResponsitory)
    {
        $this->statisAttendance = $statisAttendanceResponsitory;
    }
    public function index(GetUserRepository $getUserRepository)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;

        $showUser = $getUserRepository->getUser($user_id, $type);
        if (!$showUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $statis = $this->statisAttendance->getStatisAttendanceSchoolOnDay();

        if (!empty($statis)) {
            return $this->responseSuccess($statis, trans('api.StatisAttendance.showSchool.success'));
        } else {
            return $this->responseError(trans('api.StatisAttendance.showSchool.errors'));
        }
    }

    public function getAllAttendanceOnWeek(GetUserRepository $getUserRepository){
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;

        $showUser = $getUserRepository->getUser($user_id, $type);
        if (!$showUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $statis = $this->statisAttendance->getStatisAttendanceSchoolOnWeek();

        if (!empty($statis)) {
            return $this->responseSuccess($statis, trans('api.StatisAttendance.showShoolOnWeek.success'));
        } else {
            return $this->responseError(trans('api.StatisAttendance.showShoolOnWeek.errors'));
        }
    }

    public function getAllAttendanceOnMonth(GetUserRepository $getUserRepository){
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;

        $showUser = $getUserRepository->getUser($user_id, $type);
        if (!$showUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $statis = $this->statisAttendance->getStatisAttendanceSchoolOnMonth();

        if (!empty($statis)) {
            return $this->responseSuccess($statis, trans('api.StatisAttendance.showShoolOnMonth.success'));
        } else {
            return $this->responseError(trans('api.StatisAttendance.showShoolOnMonth.errors'));
        }
    }

    public function getAllAttendanceOnDayWithClass(GetUserRepository $getUserRepository){
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;

        $showUser = $getUserRepository->getUser($user_id, $type);
        if (!$showUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $statis = $this->statisAttendance->getStatisAttendanceSchoolOnDayWithClass();

        if (!empty($statis)) {
            return $this->responseSuccess($statis, trans('api.StatisAttendance.showShoolOnDayWithClass.success'));
        } else {
            return $this->responseError(trans('api.StatisAttendance.showShoolOnDayWithClass.errors'));
        }
    }

    public function getAllAttendanceOnWeekyWithClass(GetUserRepository $getUserRepository){
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;

        $showUser = $getUserRepository->getUser($user_id, $type);
        if (!$showUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $statis = $this->statisAttendance->getStatisAttendanceSchoolOnWeekWithClass();

        if (!empty($statis)) {
            return $this->responseSuccess($statis, trans('api.StatisAttendance.showShoolOnWeekWithClass.success'));
        } else {
            return $this->responseError(trans('api.StatisAttendance.showShoolOnWeekWithClass.errors'));
        }
    }

    public function getAllAttendanceOnMonthyWithClass(GetUserRepository $getUserRepository){
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;

        $showUser = $getUserRepository->getUser($user_id, $type);
        if (!$showUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $statis = $this->statisAttendance->getStatisAttendanceSchoolOnMonthWithClass();

        if (!empty($statis)) {
            return $this->responseSuccess($statis, trans('api.StatisAttendance.showShoolOnMonthWithClass.success'));
        } else {
            return $this->responseError(trans('api.StatisAttendance.showShoolOnMonthWithClass.errors'));
        }
    }
}
