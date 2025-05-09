<?php

namespace App\Domain\RollCallHistory\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\RollCallHistory\Repository\RollCallHistoryTeacherRepository;
use App\Http\Controllers\BaseController;
use App\Models\Classes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RollCallHistoryTeacherController extends BaseController
{
    protected $rollCallHistoryRepository;
    private $user;

    public function __construct(Request $request, RollCallHistoryTeacherRepository $rollCallHistoryRepository)
    {
        $this->user = new GetUserRepository();
        $this->rollCallHistoryRepository = $rollCallHistoryRepository;
    }


    public function getClass(Request $request, GetUserRepository $getUserRepository)
    {
        $user_id = Auth::user()->id;
        $type    = AccessTypeEnum::TEACHER->value;


        $showUser = $getUserRepository->getUser($user_id, $type);
        if (!$showUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $classTeachers = $this->rollCallHistoryRepository->getClassTeacher($user_id);
        return $this->responseSuccess($classTeachers);
    }

    public function showRollCallHistories(Request $request, $classId)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::TEACHER->value;

        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $pageSize = $request->input('pageSize', 10);
        if (!is_numeric($pageSize) || $pageSize <= 0) {
            return response()->json(['message' => 'Yêu cầu nhập số lượng lớn hơn 0'], 400);
        }

        $keyWord = $request->input('keyword', null);
        $Date = $request->input('date', null);
        $histories = $this->rollCallHistoryRepository->getClassRollCallHistories($classId, $pageSize, $keyWord, $Date,$user_id);

        return response()->json($histories);
    }

    public function studentInClass($class_id, $teacher_subject_timetable_id, Request $request)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::TEACHER->value;
        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }
        // Lấy tham số name và student_code từ request
        $name         = $request->input('name', null);         // Tên học sinh
        $student_code = $request->input('student_code', null); // Mã học sinh
        $date         = isset($request->date) ? Carbon::parse($request->date) : Carbon::now();
        // Gọi repository để lấy danh sách học sinh theo lớp và tham số tìm kiếm
        $student = $this->rollCallHistoryRepository->getClassRollCallHistoryDetailsByDate($class_id, $teacher_subject_timetable_id);
        // Kiểm tra và trả về kết quả
        if ($student) {
            return $this->responseSuccess($student, trans('api.rollcall.index.success'));
        } else {
            return $this->responseError(trans('api.rollcall.index.errors'));
        }
    }
}
