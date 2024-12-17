<?php
namespace App\Domain\ParentRollCallHistory\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Repository\GetUserRepository;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Domain\ParentRollCallHistory\Repository\ParentRollCallHistoryRepository;
use Illuminate\Support\Facades\Auth;

class ParentRollCallHistoryController extends BaseController
{
    protected $ParentRollCallHistoryRepository;
    private $user;
    public function __construct(Request $request, ParentRollCallHistoryRepository $parentRollCallHistoryRepository)
    {
        $this->user=  new GetUserRepository();
        parent::__construct( $request);
        $this->ParentRollCallHistoryRepository = $parentRollCallHistoryRepository;
    }
    public function index(Request $request)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::GUARDIAN->value;
    
        // Kiểm tra quyền truy cập của phụ huynh
        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }
 
            $userId = $request->user()->id;  // Lấy user_id của phụ huynh từ request
            $studentId = $request->query('studentId');  // Lấy studentId từ query parameter
            $pageSize = $request->query('pageSize', 10);  // Lấy pageSize từ query parameter, mặc định là 10
            $keyWord = $request->query('keyWord');  // Lấy từ khóa tìm kiếm từ query parameter
            $date = $request->query('date');  // Lấy ngày từ query parameter
    
            // Gọi repository để lấy dữ liệu
            $result = $this->ParentRollCallHistoryRepository->getParentStudentRollCallHistories(
                $userId, $pageSize, $keyWord, $date, $studentId
            );
    
            return response()->json($result);
        }
    
}
            