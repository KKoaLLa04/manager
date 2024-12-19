<?php
namespace App\Domain\Auth\Controllers;

use App\Common\Enums\StatusEnum;
use App\Common\Repository\GetSchoolYearRepository;
use App\Domain\Auth\Repository\LoginRepository;
use App\Domain\Auth\Requests\DeviceRequest;
use App\Domain\Auth\Requests\LoginRequest;
use App\Http\Controllers\BaseController;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AuthController extends BaseController
{
    public function __construct(
        Request $request,
        protected LoginRepository $loginRepository,
        protected GetSchoolYearRepository $getSchoolYearRepository
    )
    {
        parent::__construct($request);
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }
    public function login(LoginRequest $loginRequest)
    {
        $credentials = $loginRequest->only('username', 'password');
        $username = $credentials['username'];
        $user = $this->loginRepository->checkLogin($username);
        if(is_null($user)){
            return $this->responseError(trans('api.error.not_found'),ResponseAlias::HTTP_UNAUTHORIZED);
        }
        if (! $token = auth()->attempt($loginRequest->all())) {
            return $this->responseError(trans('api.error.not_found'),ResponseAlias::HTTP_UNAUTHORIZED);
        }
        if($user->status == StatusEnum::UN_ACTIVE->value){
            return $this->responseError(trans('api.error.not_found'),ResponseAlias::HTTP_UNAUTHORIZED);
        }
        $studentOfUser = $this->loginRepository->getStudentOfUser($user);
        $schoolYear = $this->getSchoolYearRepository->getSchoolYear();
        $dataResponse = $this->loginRepository->transform($user, $studentOfUser, $token,$schoolYear);
        return $this->responseSuccess($dataResponse);
    }

    public function storeDeviceToken(DeviceRequest $request)
    {
        $checkExits = UserDevice::query()
            ->where('user_id', auth()->id())
            ->where('device_token', $request->device_token)
            ->where('device_type', $request->device_type)
            ->where('status', StatusEnum::ACTIVE->value)
            ->exists();
        if (!$checkExits){
            UserDevice::query()->create([
                "user_id" => auth()->id(),
                "device_token" => $request->device_token,
                "device_type" => $request->device_type,
                "status" => StatusEnum::ACTIVE->value
            ]);
        }
    }
}
