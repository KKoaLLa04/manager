<?php

namespace App\Domain\Guardian\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\Guardian\Repository\GuardianOfGuardianRespository;
use App\Domain\Guardian\Requests\GuardianLayoutGuardianRequest;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuardianOfGuardianController extends BaseController
{
    protected $guardianRepository;
    public function __construct(Request $request)
    {
        $this->guardianRepository = new GuardianOfGuardianRespository();
    }

    public function show(GetUserRepository $getUserRepository)
    {
        // Lấy ID của người dùng hiện tại
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::GUARDIAN->value;

        // Kiểm tra xem người dùng có quyền truy cập hay không
        $getUser = $getUserRepository->getUser($user_id, $type);
        if (!$getUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        // Lấy thông tin của Guardian
        $showoneGuardian = $this->guardianRepository->getOneGuardian();

        // Kiểm tra nếu không tìm thấy Guardian
        if ($showoneGuardian) {
            return $this->responseSuccess($showoneGuardian, trans('api.guardianofguardian.show.success'));
        } else {
            return $this->responseError(trans('api.guardianofguardian.show.errors'));
        }
    }

    public function getStudentInGuardian()
{
    $response = $this->guardianRepository->getStudentInGuardian();
    return response()->json($response);
}

}
