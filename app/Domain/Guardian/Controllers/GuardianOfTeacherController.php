<?php

namespace App\Domain\Guardian\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\Guardian\Repository\GuardianOfTeacherRepository;
use App\Domain\Guardian\Requests\GuardianLayoutTeacherRequest;
use App\Domain\Guardian\Requests\GuardianRequest;
use App\Http\Controllers\BaseController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class GuardianOfTeacherController extends BaseController
{
    protected $guardianRepository;
    public function __construct(Request $request)
    {
        $this->guardianRepository = new GuardianOfTeacherRepository();
    }

    public function LockGuardian(int $id, GetUserRepository $getUserRepository, Request $request){
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;


        $getUser = $getUserRepository->getUser($user_id, $type);
        if (!$getUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $lock = $this->guardianRepository->lockGuardian($id);
        if($lock){
            return $this->responseSuccess([],trans('api.guardian.lock.success'));
        }else{
            return $this->responseError(trans('api.guardian.lock.errors'));
        }
    }

    public function UnLockGuardian(int $id, GetUserRepository $getUserRepository, Request $request){
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;

        $getUser = $getUserRepository->getUser($user_id, $type);
        if (!$getUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $unlock = $this->guardianRepository->unlockGuardian($id);
        if($unlock){
            return $this->responseSuccess([],trans('api.guardian.unlock.success'));
        }else{
            return $this->responseError(trans('api.guardian.unlock.errors'));
        }
    }

    public function changePasswordGuardian(int $id, GetUserRepository $getUserRepository, Request $request)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;


        $getUser = $getUserRepository->getUser($user_id, $type);
        if (!$getUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }


        $dataUpdate = [
            'password' => Hash::make($request->password),
            'modified_user_id' => $user_id,
            'updated_at' => now(),
        ];


        $passwordUpdate = $this->guardianRepository->changePassword($id, $dataUpdate);

        if ($passwordUpdate) {
            return $this->responseSuccess([],trans('api.guardian.change_password.success'));
        } else {
            return $this->responseError(trans('api.guardian.change_password.errors'));
        }
    }

    public function update(int $id, GuardianLayoutTeacherRequest $request, GetUserRepository $getUserRepository) {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;


        $getUser = $getUserRepository->getUser($user_id, $type);
        if (!$getUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }


        $dataUpdate = [
            'fullname' => $request->fullname,
            'phone' => $request->phone,
            'email'=> $request->email,
            'dob' => Carbon::parse($request->dob),
            'status' => $request->status,
            'gender' => $request->gender,
            'address' => $request->address,
            'modified_user_id' => $user_id,
            'updated_at' => now(),
        ];


        $update = $this->guardianRepository->updateGuardian($id, $dataUpdate);
        if ($update) {
            return $this->responseSuccess(['data' => []], trans('api.guardian.edit.success'));
        } else {
            return $this->responseError(trans('api.guardian.edit.errors'));
        }
    }

}
