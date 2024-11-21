<?php
namespace App\Domain\RollcallStatistics\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\RollcallStatistics\Repository\RollcallStatisticsRepository;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
      
        $classes = $this->rollCallStatistics->listClasses($pageSize, $keyWord);

        return response()->json([
            'message' => 'Lấy danh sách lớp thành công',
            'status' => 'success',
            'total_classes' => $classes['total'],
            'data' => $classes['data'],
            'page_index' => $classes['current_page'],
            'page_size' => $classes['per_page'],
        ]);
    }

    public function showclassRollCall( Request $request, $classId){
        
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
        $histories = $this->rollCallStatistics->getClassRollCall($classId, $pageSize, $keyWord, $Date);

        return response()->json($histories);
    }
}
            