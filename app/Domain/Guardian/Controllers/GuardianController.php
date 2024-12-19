<?php
namespace App\Domain\Guardian\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\Guardian\Models\Guardian;
use App\Domain\Guardian\Repository\GuardianRepository;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Domain\Guardian\Requests\GuardianRequest;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class GuardianController extends BaseController
{
    protected $guardianRepository;
    public function __construct(Request $request)
    {
        $this->guardianRepository = new GuardianRepository();
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

        $guardians = $this->guardianRepository->getGuardian($keyword, $pageIndex, $pageSize);

        if (!empty($guardians)) {
            return $this->responseSuccess($guardians, trans('api.guardian.index.success'));
        } else {
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
        'code' => Guardian::generateRandomCode(),
        'email' => $request->email,
        'access_type' => AccessTypeEnum::GUARDIAN->value,
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
            return $this->responseSuccess(['data' => []], trans('api.guardian.add.success'));
        }else{
            return $this->responseError(trans('api.guardian.add.errors'));
        }
    }

    public function show(int $id, GetUserRepository $getUserRepository, Request $request)
{
    $user_id = Auth::user()->id;
    $type = AccessTypeEnum::MANAGER->value;


    $getUser = $getUserRepository->getUser($user_id, $type);
    if (!$getUser) {
        return $this->responseError(trans('api.error.user_not_permission'));
    }


    $showoneGuardian = $this->guardianRepository->getOneGuardian($id);


    if ($showoneGuardian['data'] === null) {
        return $this->responseError(trans('api.guardian.show.errors'));
    }


    return $this->responseSuccess($showoneGuardian['data'], trans('api.guardian.show.success'));
}


    public function getStudent(Request $request)
{
    $keyword = $request->get('keyword', null);
    $pageIndex = $request->get('pageIndex', 1);
    $pageSize = $request->get('pageSize', 10);


    $students = $this->guardianRepository->getStudent($keyword, $pageIndex, $pageSize);

    if (!empty($students)) {
        return $this->responseSuccess($students['data'], 'Lấy danh sách học sinh thành công');
    } else {
        return $this->responseError('Lấy danh sách học sinh thất bại');
    }
}


public function update(int $id, GuardianRequest $request, GetUserRepository $getUserRepository) {
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
        'access_type' => AccessTypeEnum::GUARDIAN->value,
        'dob' => $request->dob,
        'status' => $request->status,
        'gender' => $request->gender,
        'address' => $request->address,
        'career' => $request->career,
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


    if ($request->filled('username')) {
        $dataUpdate['username'] = $request->username;
    }


    $update = $this->guardianRepository->updateGuardian($id, $dataUpdate);
    if ($update) {
        return $this->responseSuccess(['data' => []], trans('api.guardian.edit.success'));
    } else {
        return $this->responseError(trans('api.guardian.edit.errors'));
    }
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
        ], 200);
    } catch (Exception $e) {
        return response()->json([
            'message' => 'Học sinh này đã có phụ huynh',
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
            'message' => 'Học sinh này đã được gỡ',
        ], 400);
    }
}


public function importExcel(Request $request)
{
    $user_id = Auth::user()->id;
    $type = AccessTypeEnum::MANAGER->value;

    $getUser = $this->guardianRepository->getUser($user_id, $type);
    if (!$getUser) {
        return $this->responseError(trans('api.error.user_not_permission'));
    }

    if (!$request->hasFile('file')) {
        return $this->responseError(trans('api.error.file_not_found'));
    }

    try {
        $file = $request->file('file');
        $data = Excel::toArray([], $file)[0]; // Lấy dữ liệu sheet đầu tiên

        $guardiansData = [];

        foreach ($data as $index => $row) {
            // Bỏ qua dòng tiêu đề
            if ($index == 0) continue;
            $dob = Carbon::parse($row[3])->format('Y-m-d');
            $guardiansData[] = [
                'fullname' => $row[0], // Cột đầu tiên trong Excel
                'phone' => $row[1], // Cột thứ hai
                'code' => Guardian::generateRandomCode(),
                'email' => $row[2],
                'access_type' => AccessTypeEnum::GUARDIAN->value,
                'dob' => $dob,
                'status' => $row[4],
                'gender' => $row[5],
                'address' => $row[6],
                'career' => $row[7],
                'username' => $row[8],
                'password' => Hash::make($row[9]),
                'created_user_id' => $user_id,
                'is_deleted' => DeleteEnum::NOT_DELETE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Guardian::insert($guardiansData);

        return $this->responseSuccess([], trans('api.guardian.import.success'));
    } catch (\Exception $e) {
        return $this->responseError(trans('api.guardian.import.errors') . $e->getMessage());
    }
}


}
