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
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::TEACHER->value;

        $showUser = $getUserRepository->getUser($user_id, $type);
        if (!$showUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

    }
}
