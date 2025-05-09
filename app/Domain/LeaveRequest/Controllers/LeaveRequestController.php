<?php
namespace App\Domain\LeaveRequest\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\LeaveRequestEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\LeaveRequest\Repository\LeaveRequestResponsitory;
use App\Domain\LeaveRequest\Requests\LeaveRequestRequest;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveRequestController extends BaseController
{
    protected $LeaveRequest;
    public function __construct(Request $request)
    {
        $this->LeaveRequest = new LeaveRequestResponsitory();

    }
    public function index(Request $request,GetUserRepository $getUserRepository)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;
        
        $getUser = $getUserRepository->getUser($user_id, $type); 
        if (!$getUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }
        $keyword = $request->get('keyword', null);
        $pageIndex = $request->get('pageIndex', 1);
        $pageSize = $request->get('pageSize', 10);
        $leaveRequest = $this->LeaveRequest->getRequest($keyword, $pageIndex, $pageSize);

        if($leaveRequest){
            return $this->responseSuccess($leaveRequest, trans('api.leaveRequest.index.success'));
        }else{
            return $this->responseError(trans('api.leaveRequest.index.errors'));
        }
    }

    public function showRequest($id,GetUserRepository $getUserRepository){

        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;
        
        $getUser = $getUserRepository->getUser($user_id, $type); 
        if (!$getUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }
        $one = $this->LeaveRequest->getOneRequest($id);

        if($one){
            return $this->responseSuccess($one, trans('api.leaveRequest.detail.success'));
        }else{
            return $this->responseError(trans('api.leaveRequest.detail.errors'));
        }
    }

    public function acceptRequest(Request $request,GetUserRepository $getUserRepository,$id){
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;
        
        $getUser = $getUserRepository->getUser($user_id, $type); 
        if (!$getUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $one = $this->LeaveRequest->accept($id);

        if($one){
            return $this->responseSuccess([], trans('api.leaveRequest.accept.success'));
        }else{
            return $this->responseError(trans('api.leaveRequest.accept.errors'));
        }
    }

    public function rejectRequest(LeaveRequestRequest $request, GetUserRepository $getUserRepository, $id)
{
    // Lấy ID của người dùng hiện tại
    $user_id = Auth::user()->id;
    $type = AccessTypeEnum::MANAGER->value;

    // Kiểm tra quyền truy cập của người dùng
    $getUser = $getUserRepository->getUser($user_id, $type);
    if (!$getUser) {
        return $this->responseError(trans('api.error.user_not_permission'), 403);
    }

    $dataUpdate = [
        'status' => LeaveRequestEnum::REJECT->value,
        'refuse_note'=>$request->refuse_note,
        'processed_by'=> auth()->id(),
        'updated_at' => now(),
    ];


    // Thực hiện từ chối yêu cầu
    $one = $this->LeaveRequest->reject($id,$dataUpdate);

    if ($one) {
        return $this->responseSuccess([], trans('api.leaveRequest.reject.success'));
    } else {
        return $this->responseError(trans('api.leaveRequest.reject.error'));
    }
}

}
            