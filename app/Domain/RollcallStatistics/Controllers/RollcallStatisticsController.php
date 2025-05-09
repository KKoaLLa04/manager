<?php
namespace App\Domain\RollcallStatistics\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\RollcallStatistics\Repository\RollcallStatisticsRepository;
use App\Http\Controllers\BaseController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class RollcallStatisticsController extends BaseController
{
    protected $rollCallStatistics;
    private $user;

    public function __construct(Request $request, RollcallStatisticsRepository $rollCallStatistics)
    {
        $this->user = new GetUserRepository();
        parent::__construct($request);
        $this->rollCallStatistics = $rollCallStatistics;
    }
  

    public function index(Request $request)
    {
        // Lấy user_id từ thông tin người dùng hiện tại
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;
        
        // Kiểm tra quyền của người dùng
        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }
    
        // Lấy giá trị pageSize từ request và kiểm tra tính hợp lệ
        $pageSize = $request->input('pageSize', 10);
        if (!is_numeric($pageSize) || $pageSize <= 0) {
            return response()->json(['message' => 'Yêu cầu nhập số lượng lớn hơn 0'], 400);
        }
    
        // Lấy từ khóa tìm kiếm (keyWord) từ request
        $keyWord = $request->input('keyWord', null);
        
        // Lấy giá trị status từ request nếu có (tùy chọn)
        $status = $request->input('status', null);
    
        $classId = $request->input('classId', null); // Nhận classId từ request

        $classes = $this->rollCallStatistics->listClasses($pageSize, $keyWord, $status, $classId);
        // Trả về dữ liệu dưới dạng JSON
        return response()->json([
            'message' => 'Lấy danh sách lớp thành công',
            'status' => 'success',
            'total_classes' => $classes['total'],
            'data' => $classes['data'],
            'page_index' => $classes['current_page'],
            'page_size' => $classes['per_page'],
        ]);
    }
    


    public function showClassRollCall(Request $request, $classId)
    {
        if (Auth::user()->access_type != AccessTypeEnum::MANAGER->value && Auth::user()->access_type != AccessTypeEnum::TEACHER->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }

        $pageSize = $request->input('pageSize', 10);
        if (!is_numeric($pageSize) || $pageSize <= 0) {
            return response()->json(['message' => 'Yêu cầu nhập số lượng lớn hơn 0'], 400);
        }

        $fromDate = !is_null($request->from_date) ? Carbon::parse($request->from_date) : clone Carbon::now()->startOfMonth();
        $toDate = !is_null($request->to_date) ? Carbon::parse($request->to_date) : Carbon::now();
        $time = !is_null($request->time) ? $request->time : 1;
        // Gọi repository
        $histories = $this->rollCallStatistics->getClassRollCall($classId, $pageSize, $fromDate,$toDate,$time);
   
        return response()->json($histories);
    }
}
