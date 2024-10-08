<?php
namespace App\Domain\Guardian\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\Guardian\Repository\GuardianRepository;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Domain\Guardian\Requests\GuardianRequest;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class GuardianController extends BaseController
{
    protected $guardianRepository;
    public function __construct(Request $request)
    {
        $this->guardianRepository = new GuardianRepository();
    }
    public function index(Request $request, GetUserRepository $getUserRepository)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;
        
        $getUser = $getUserRepository->getUser($user_id, $type); 
        if (!$getUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $keyword = "";
        
        if(!empty($request->keyword)){
            $keyword = $request->keyword;
        }

       $pageIndex = $request->get('pageIndex',1);
       $pageSize = $request->input('pageSize',10);
        $guarDian = $this->guardianRepository->getGuardian($pageSize);
        
        if($guarDian){
        return $this->responseSuccess([
            'data'=>$guarDian,
            'total' => $guarDian->total(),
            'current_page' => $guarDian->currentPage(), 
            'last_page' => $guarDian->lastPage(),
            'per_page' => $guarDian->perPage(),
        ],trans('api.guardian.index.success'));
        }else{
        return $this->responseError(trans('api.guardian.index.errors'));
        }
    }

    public function create(GuardianRequest $request, GetUserRepository $getUserRepository){
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;
        
        $getUser = $getUserRepository->getUser($user_id, $type); 
        if (!$getUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $dataInsert = [
        'fullname' => $request->fullname,
        'phone' => $request->phone,
        'code' => $request->code,
        'email' => $request->email,
        'access_type' => $request->access_type,
        'dob' => $request->dob,
        'status' => $request->status,
        'gender' => $request->gender,
        'address' => $request->address,
        'career' => $request->career,
        'username' => $request->username,
        'password' => Hash::make($request->password),
        'confirm_password'=> $request->confirm_password,
        'created_user_id' => $user_id,
        'is_deleted' => DeleteEnum::NOT_DELETE->value,
        'created_at' => now(),
    ];
        $addGuadian = $this->guardianRepository->addGuardian($dataInsert);

        if($addGuadian){
            return $this->responseSuccess(['data' => $addGuadian], trans('api.guardian.add.success'));
        }else{
            return $this->responseError(trans('api.guardian.add.errors'));
        }
    }

    public function show(int $id, GetUserRepository $getUserRepository, Request $request){
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;
        
        $getUser = $getUserRepository->getUser($user_id, $type); 
        if (!$getUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $showoneGuardian = $this->guardianRepository->getOneGuardian($id);

        if($showoneGuardian){
            return $this->responseSuccess(['data' => $showoneGuardian], trans('api.guardian.show.success'));
        }else{
            return $this->responseError(trans('api.guardian.show.errors'));
        }
    }

    public function update(int $id, GuardianRequest $request, GetUserRepository $getUserRepository) {
                $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;
        
        // Kiểm tra quyền truy cập của người dùng
        $getUser = $getUserRepository->getUser($user_id, $type); 
        if (!$getUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }
    
        // Chuẩn bị dữ liệu cập nhật
        $dataUpdate = [
            'fullname' => $request->fullname,
            'phone' => $request->phone,
            'email'=> $request->email,
            'access_type' => $request->access_type,
            'dob' => $request->dob,
            'status' => $request->status,
            'gender' => $request->gender,
            'address' => $request->address,
            'career' => $request->career,
            'username' => $request->username,
            'modified_user_id' => $user_id,
            'updated_at' => now(),
        ];
    
        
        if ($request->filled('password')) {
            if ($request->password === $request->confirm_password) {
                $dataUpdate['password'] = Hash::make($request->password);
            } else {
                return $this->responseError(trans('api.guardian.password_mismatch'));
            }
        }
    
        
        $update = $this->guardianRepository->updateGuardian($id, $dataUpdate);
        if ($update) {
            return $this->responseSuccess(['data' => $dataUpdate], trans('api.guardian.edit.success'));
        } else {
            return $this->responseError(trans('api.guardian.edit.errors'));
        }
    }

    public function LockGuardian(int $id, GetUserRepository $getUserRepository, Request $request){
                $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;
        
        // Kiểm tra quyền truy cập của người dùng
        $getUser = $getUserRepository->getUser($user_id, $type); 
        if (!$getUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }
        
        $lock = $this->guardianRepository->lockGuardian($id);
        if($lock){
            return $this->responseError( trans('api.guardian.lock.success'));
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

        $lock = $this->guardianRepository->unlockGuardian($id);
        if($lock){
            return $this->responseError(trans('api.guardian.unlock.success'));
        }else{
            return $this->responseError(trans('api.guardian.unlock.errors'));
        }
    }

    public function ChangePasswordGuardian(int $id, GetUserRepository $getUserRepository, Request $request){
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;
        
        $getUser = $getUserRepository->getUser($user_id, $type); 
        if (!$getUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $dataUpdate = [
            'password' => Hash::make($request->password),
            'confirm_password'=> $request->confirm_password,
            'modified_user_id' => $user_id,
            'updated_at' => now(),
        ];

        $passwordUpdate = $this->guardianRepository->changePassword($id,$dataUpdate);

        if($passwordUpdate){
            return $this->responseSuccess(['data'=>$passwordUpdate],trans('api.guardian.change_password.success'));
        }else{
            return $this->responseError(trans('api.guardian.change_password.errors'));
        }
    }

    public function assignStudent(Request $request, GetUserRepository $getUserRepository, $guardianId)
{
    $user_id = Auth::user()->id;
    $type = AccessTypeEnum::MANAGER->value;
    
    $getUser = $getUserRepository->getUser($user_id, $type); 
    if (!$getUser) {
        return $this->responseError(trans('api.error.user_not_permission'));
    }

    $studentIds = $request->input('student_id');

    try {
        $data = $this->guardianRepository->assignStudent($guardianId, $studentIds, $user_id);

        return response()->json([
            'message' => 'Học sinh được gán thành công cho phụ huynh '.$data['guardian']->fullname,
            'guardian' => $data['guardian'],
            'assigned_students' => $data['assigned_students'],
            'already_assigned' => $data['already_assigned']
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            'message' => $e->getMessage()
        ], 400);
    }
}


public function unassignStudent(Request $request, int $guardianId, GetUserRepository $getUserRepository)
{
    $user_id = Auth::user()->id;
    $type = AccessTypeEnum::MANAGER->value;
    
    $getUser = $getUserRepository->getUser($user_id, $type); 
    if (!$getUser) {
        return $this->responseError(trans('api.error.user_not_permission'));
    }
    $studentIds = $request->input('student_id');

    try {
       $this->guardianRepository->unassignStudent($guardianId, $studentIds);

        return response()->json([
            'message' => 'Học sinh đã được gỡ khỏi phụ huynh thành công.'
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            'message' => 'Gỡ phụ huynh thất bại',
        ], 400);
    }
}


    
}
            