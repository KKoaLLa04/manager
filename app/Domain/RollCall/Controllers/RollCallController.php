<?php
namespace App\Domain\RollCall\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\RollCall\Models\RollCall;
use App\Domain\RollCall\Repository\RollCallRepository;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
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
    
    $timestamp = $request->input('timestamp');

   $keyword = $request->input('keyword');
    $pageIndex = $request->input('pageIndex', 1);
    $pageSize = $request->input('pageSize', 10);

    
    $rollCalls = $this->rollCallRepository->getAllRollCalls($keyword,$timestamp, $pageIndex, $pageSize);

    
    if ($rollCalls) {
        return $this->responseSuccess($rollCalls, trans('api.rollcall.index.success'));
    } else {
        return $this->responseError(trans('api.rollcall.index.error'));
    }
}

}
            