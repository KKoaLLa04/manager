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

    public function show(int $id, GetUserRepository $getUserRepository, Request $request)
{
    $user_id = Auth::user()->id;
    $type = AccessTypeEnum::GUARDIAN->value;

    $getUser = $getUserRepository->getUser($user_id, $type);
    if (!$getUser) {
        return $this->responseError(trans('api.error.user_not_permission'));
    }

    if ($user_id != $id) {
        return $this->responseError(trans('api.guardianofguardian.show.access_denied'));
    }

    $showoneGuardian = $this->guardianRepository->getOneGuardian($id);

    if ($showoneGuardian['data'] === null) {
        return $this->responseError(trans('api.guardianofguardian.show.errors'));
    }

    return $this->responseSuccess($showoneGuardian['data'], trans('api.guardianofguardian.show.success'));
}


public function update(int $id, GuardianLayoutGuardianRequest $request, GetUserRepository $getUserRepository) {
    $user_id = Auth::user()->id;
    $type = AccessTypeEnum::GUARDIAN->value;

    
    $getUser = $getUserRepository->getUser($user_id, $type); 
    if (!$getUser) {
        return $this->responseError(trans('api.error.user_not_permission'));
    }

    if ($user_id != $id) {
        return $this->responseError(trans('api.guardianofguardian.show.access_denied'));
    }
    
    $dataUpdate = [
        'fullname' => $request->fullname,
        'phone' => $request->phone,
        'dob' => $request->dob,
        'address' => $request->address,
        'modified_user_id' => $user_id,
        'updated_at' => now(),
    ];
 
    $update = $this->guardianRepository->updateGuardianProfile($dataUpdate, $id);
    if ($update) {
        return $this->responseSuccess(['data' => []], trans('api.guardianofguardian.update.success'));
    } else {
        return $this->responseError(trans('api.guardianofguardian.update.errors'));
    }
}
    
}
            