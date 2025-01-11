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

    public function showRollCallHistoryDetails(Request $request)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;

        // Kiểm tra quyền truy cập của người dùng
        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $classId = $request->input('class_id');
        $timetableId = $request->input('timetable_id');
        $date = $request->input('date', Carbon::now()->toDateString()); // Lấy ngày nếu có, mặc định là ngày hiện tại

        // Lấy lịch sử điểm danh lớp
        $rollCallHistory = $this->rollCallHistoryRepository->getClassRollCallHistoryDetailsByDate($classId, $timetableId, $date);

        // Trả kết quả nếu có dữ liệu
        if ($rollCallHistory->isNotEmpty()) {
            return $this->responseSuccess($rollCallHistory, trans('api.rollcall.history.success'));
        } else {
            return $this->responseError(trans('api.rollcall.history.not_found'));
        }
    }
}
