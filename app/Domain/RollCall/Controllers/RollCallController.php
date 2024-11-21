<?php

namespace App\Domain\RollCall\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\RollCall\Models\RollCall;
use App\Domain\RollCall\Repository\RollCallRepository;
use App\Domain\RollCall\Requests\RollCallRequest;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class RollCallController extends BaseController
{

    protected $rollCallRepository;

    public function __construct(RollCallRepository $rollCallRepository)
    {
        $this->rollCallRepository = $rollCallRepository;
    }
    public function index(Request $request, GetUserRepository $getUserRepository)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;

        $showUser = $getUserRepository->getUser($user_id, $type);
        if (!$showUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $pageIndex = $request->input('pageIndex', 1); // Mặc định là trang 1
        $pageSize = $request->input('pageSize', 10); // Mặc định số bản ghi trên mỗi trang
        $keyWord = $request->input('keyWord', null); // Từ khóa tìm kiếm
        $date = $request->input('date', null); // Ngày điểm danh

        $rollCalls = $this->rollCallRepository->getClass($pageIndex, $pageSize, $keyWord, $date);

        if ($rollCalls) {
            return $this->responseSuccess($rollCalls, trans('api.rollcall.index.success'));
        } else {
            return $this->responseError(trans('api.rollcall.index.errors'));
        }
    }

    public function rollCall(Request $request, $classId, GetUserRepository $getUserRepository)
    {

        $user = Auth::user();
        if (!$user) {
            return $this->responseError(trans('api.error.user_not_logged_in'));
        }

        $user_id = $user->id;
        $type = AccessTypeEnum::MANAGER->value;


        $showUser = $getUserRepository->getUser($user_id, $type);
        if (!$showUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $rollCallData = $request->input('rollcallData', []);

        $studentClassDetails = $this->rollCallRepository->getStudentClassDetails($classId, $rollCallData, $user_id);

        if (is_array($studentClassDetails) && !empty($studentClassDetails['insert_roll_call'])) {
            return $this->responseSuccess([], trans('api.rollcall.attendaced.success'));
        } else {
            return $this->responseError(trans('api.rollcall.attendaced.errors'));
        }
    }

    public function updateByClass(Request $request, $class_id, GetUserRepository $getUserRepository)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;

        $showUser = $getUserRepository->getUser($user_id, $type);
        if (!$showUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $validatedData = $request->validate([
            'students' => 'required|array',
            'students.*.student_id' => 'required|integer|exists:students,id',
            'students.*.status' => 'required|integer',
            'students.*.note' => 'nullable|string',
        ]);

        [$totalStudent, $totalStudentAttendaced, $totalStudentNotAttendaced, $rollCalls] = $this->rollCallRepository->updateByClass($class_id, $validatedData['students'], $user_id);
        $data = [
            'total_student' => $totalStudent,
            'total_student_attended' => $totalStudentAttendaced,
            'total_student_not_attended' => $totalStudentNotAttendaced,
            'updated_roll_calls' => $rollCalls,
        ];

        return $this->responseSuccess($data, trans('api.rollcall.attendaced_updated.success'));
    }

    public function getRowCallOfClass(RollCallRequest $request)
    {
        $keyWord = $request->input('keyWord', "");
        $classId = $request->input('classId', 0);
        $date = $request->input('date');
        $date = isset($date) ? Carbon::parse($date) : now();
        list($students,$total) = $this->rollCallRepository->getStudentClass($classId, $keyWord);
        $rollCall = $this->rollCallRepository->getRollCall($classId, $students, $date);
        return $this->responseSuccess(
            [
                "rollCall" => $rollCall,
                "totalStudent" => $total,
            ]
        );
    }
}
