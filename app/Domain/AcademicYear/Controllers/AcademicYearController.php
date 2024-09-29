<?php

namespace App\Domain\AcademicYear\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\AcademicYear\Models\AcademicYear;
use App\Domain\AcademicYear\Repository\AcademicYearReposity;
use App\Domain\AcademicYear\Requests\AcademicYearRequest;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class AcademicYearController extends BaseController
{
    protected $academicYearRepository;
    

    public function __construct(AcademicYearReposity $academicYearRepository)
    {
        $this->academicYearRepository = $academicYearRepository;
        
    }

    
    public function index(Request $request, GetUserRepository $getUserRepository)
    {
        $user_id = $request->user_id;
        $type = AccessTypeEnum::MANAGER->value;
        
        $getUser = $getUserRepository->getUser($user_id, $type); 
        if (!$getUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }
        $academicYears = $this->academicYearRepository->getAcademicYear();
        
        
        return response()->json([
            'success' => true,
            'data' => $academicYears,
        ], 200);
    }

    public function show(int $id, Request $request, GetUserRepository $getUserRepository){
        $user_id = $request->user_id;
        $type = AccessTypeEnum::MANAGER->value;
        
        $showUser = $getUserRepository->getUser($user_id, $type); 
        if (!$showUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }
        $academicYear = $this->academicYearRepository->findById($id);

        if ($academicYear) {
            return response()->json([
               'success' => true,
                'data' => $academicYear,
            ], 200);
        } else {
            return response()->json([
               'success' => false,
               'message' => 'Không tìm thấy niên khóa!',
            ], 400);
        }
    }
    
    public function store(AcademicYearRequest $request, GetUserRepository $getUserRepository)
{
    $user_id = $request->user_id;
    $type = AccessTypeEnum::MANAGER->value;
    
    $createdUser = $getUserRepository->getUser($user_id, $type); 
    if (!$createdUser) {
        return $this->responseError(trans('api.error.user_not_permission'));
    }

    $dataInsert = [
        'name' => $request->name,
        'code' => AcademicYear::generateRandomCode(),
        'start_year' => $request->start_year,
        'end_year' => $request->end_year,
        'status' => $request->status,
        'created_user_id' => $user_id,
        'created_at' => now(),
    ];

    
    $item = $this->academicYearRepository->create($dataInsert);

    if ($item) {
        return response()->json([
            'success' => true,
            'message' => 'Thêm niên khóa thành công!',
            'data' => $item,
        ], 201);
    } else {
        return response()->json([
            'success' => false,
            'message' => 'Thêm niên khóa thất bại!',
        ], 400);
    }
}



public function update(AcademicYearRequest $request, int $id, GetUserRepository $getUserRepository)
    {
        $user_id = $request->user_id;
        $type = AccessTypeEnum::MANAGER->value;
        
        $modifiedUser = $getUserRepository->getUser($user_id, $type); 
        if (!$modifiedUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }
        $dataUpdate = [
            'name' => $request->name,
            'status' => $request->status,
            'start_year'=> $request->start_year,
            'end_year'=> $request->end_year,
            'modified_user_id' => $user_id,
            'updated_at' => now(),
        ];

        
        $item = $this->academicYearRepository->update($id, $dataUpdate);

        if ($item) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật niên khóa thành công!',
                'data' => $item,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Niên khóa không tồn tại',
            ], 400);
        }
    }


    public function delete(Request $request, int $id, GetUserRepository $getUserRepository)
    {
        
        $user_id = $request->user_id;
        
        $type = AccessTypeEnum::MANAGER->value;
    
        
        $deleteUser = $getUserRepository->getUser($user_id, $type); 
        if (!$deleteUser) {
            
            return $this->responseError(trans('api.error.user_not_permission'));
        }
    
        // Thực hiện xóa mềm với id niên khóa và user_id
        $deletedAcademicYear = $this->academicYearRepository->softDelete($id, $user_id);
    
        // Nếu xóa thành công
        if ($deletedAcademicYear) {
            return response()->json([
                'success' => true,
                'message' => 'Xóa niên khóa thành công!',
                'data' => $deletedAcademicYear,
            ], 200);
        }
    
        // Nếu không thành công, trả về lỗi
        return response()->json([
            'success' => false,
            'message' => 'Niên khóa không tôn tại hoặc vẫn còn hoạt động!',
        ], 400); 
    }
    

    

}
