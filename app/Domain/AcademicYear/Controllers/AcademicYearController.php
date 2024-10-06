<?php

namespace App\Domain\AcademicYear\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\AcademicYear\Models\AcademicYear;
use App\Domain\AcademicYear\Repository\AcademicYearReposity;
use App\Domain\AcademicYear\Requests\AcademicYearRequest;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $pageIndex = 1;

        if(!empty($request->pageIndex)){
            $pageIndex = $request->pageIndex;
        }

        $pageSize = 15;

        if(!empty($request->pageSize)){
            $pageSize = $request->pageSize;
        }

        $academicYears = $this->academicYearRepository->getAcademicYear();
        
        if($academicYears){
        return $this->responseSuccess(['data'=>$academicYears->forPage($pageIndex,$pageSize)],trans('api.academic_year.index.success'));
        }else{
        return $this->responseError(trans('api.academic_year.index.errors'));
        }
    }

    public function show(int $id, Request $request, GetUserRepository $getUserRepository){
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;
        
        $showUser = $getUserRepository->getUser($user_id, $type); 
        if (!$showUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }
        $academicYear = $this->academicYearRepository->findById($id);

        if($academicYear){
            response()->json([
                'data' => $academicYear,
                'message' => true
            ]);
        }
    }
    
    public function store(AcademicYearRequest $request, GetUserRepository $getUserRepository)
{
    $user_id = Auth::user()->id;
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

    if($item){
        return $this->responseSuccess(['data'=> $item],trans('api.academic_year.add.success'));
    }else{
        return $this->responseError(trans('api.academic_year.add.errors'));
    }
}



public function update(AcademicYearRequest $request, int $id, GetUserRepository $getUserRepository)
    {
                $user_id = Auth::user()->id;
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

        if($item){
            return $this->responseSuccess(['data'=> $item],trans('api.academic_year.update.success'));
        }else{
            return $this->responseError(trans('api.academic_year.update.errors'));
        }
    }


    public function delete(Request $request, int $id, GetUserRepository $getUserRepository)
    {
        
                $user_id = Auth::user()->id;
        
        $type = AccessTypeEnum::MANAGER->value;
    
        
        $deleteUser = $getUserRepository->getUser($user_id, $type); 
        if (!$deleteUser) {
            
            return $this->responseError(trans('api.error.user_not_permission'));
        }
    
        // Thực hiện xóa mềm với id niên khóa và user_id
        $deletedAcademicYear = $this->academicYearRepository->softDelete($id, $user_id);
    
        if($deletedAcademicYear){
            return $this->responseSuccess(['data'=> $deletedAcademicYear],trans('api.academic_year.delete.success'));
        }else{
            return $this->responseError(trans('api.academic_year.delete.errors'));
        }
    }
    

    

}

