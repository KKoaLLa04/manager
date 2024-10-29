<?php
namespace App\Domain\RollCallHistory\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\RollCallHistory\Repository\RollCallHistoryRepository;
use App\Http\Controllers\BaseController;
use App\Models\Classes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RollCallHistoryController extends BaseController
{
    protected $rollCallHistoryRepository;
    private $user;

    public function __construct(Request $request, RollCallHistoryRepository $rollCallHistoryRepository)
    {
        $this->user = new GetUserRepository();
        parent::__construct($request);
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
        $keyWord = $request->input('keyWord', null);
        $Date = $request->input('date', null);
        $histories = $this->rollCallHistoryRepository->getClassRollCallHistories($classId, $pageSize, $keyWord, $Date);

        return response()->json($histories);
    }

    public function showRollCallHistoryDetails(Request $request, $classId)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;
        
        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        // Kiểm tra nếu ngày không được cung cấp
        $date = $request->input('date');
        if (!$date) {
            return response()->json(['message' => 'Yêu cầu cung cấp ngày cụ thể'], 400);
        }

        // Gọi tới repository để lấy chi tiết điểm danh
        $details = $this->rollCallHistoryRepository->getClassRollCallHistoryDetailsByDate($classId, $date);

        return response()->json($details);
    }

}
