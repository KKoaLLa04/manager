<?php

namespace App\Domain\RollCallHistory\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\RollCallHistory\Repository\RollCallHistoryRepository;
use App\Http\Controllers\BaseController;
use App\Models\Classes;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RollCallHistoryController extends BaseController
{
    protected $rollCallHistoryRepository;
    private $user;

    public function __construct(Request $request, RollCallHistoryRepository $rollCallHistoryRepository)
    {
        $this->user = new GetUserRepository();
        $this->rollCallHistoryRepository = $rollCallHistoryRepository;
    }


    public function index(Request $request)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;

        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }


        $pageSize = $request->input('pageSize', 10);
        if (!is_numeric($pageSize) || $pageSize <= 0) {
            return response()->json(['message' => 'Yêu cầu nhập số lượng lớn hơn 0'], 400);
        }
        $keyWord = $request->input('keyWord', null);

        $classes = $this->rollCallHistoryRepository->getClassesWithRollCallHistories($pageSize, $keyWord);

        return response()->json([
            'message' => 'Lấy danh sách lớp thành công',
            'status' => 'success',
            'total_classes' => $classes['total'],
            'data' => $classes['data'],
            'page_index' => $classes['current_page'],
            'page_size' => $classes['per_page'],
        ]);
    }



    public function showRollCallHistories(Request $request, $classId)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;

        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $pageSize = $request->input('pageSize', 10);
        if (!is_numeric($pageSize) || $pageSize <= 0) {
            return response()->json(['message' => 'Yêu cầu nhập số lượng lớn hơn 0'], 400);
        }

        $keyWord = $request->input('keyword', null);
        $Date = $request->input('date', null);
        $histories = $this->rollCallHistoryRepository->getClassRollCallHistories($classId, $pageSize, $keyWord, $Date);

        return response()->json($histories);
    }

    public function studentInClass($class_id, $teacher_subject_timetable_id, Request $request)
    {
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
