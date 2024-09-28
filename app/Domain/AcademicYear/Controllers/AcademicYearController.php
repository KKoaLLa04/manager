<?php

namespace App\Domain\AcademicYear\Controllers;

use App\Domain\AcademicYear\Models\AcademicYear;
use App\Domain\AcademicYear\Repository\AcademicYearReposity;
use App\Domain\AcademicYear\Requests\AcademicYearRequest;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;

class AcademicYearController extends BaseController
{
    protected $academicYearRepository;

    public function __construct(AcademicYearReposity $academicYearRepository)
    {
        $this->academicYearRepository = $academicYearRepository;
    }

    
    public function index()
    {
        
        $academicYears = $this->academicYearRepository->getAcademicYear();
        
        
        return response()->json([
            'success' => true,
            'data' => $academicYears,
        ], 200);
    }

    public function show($id){
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
            ], 404);
        }
    }
    
    public function store(AcademicYearRequest $request)
    {
        $dataInsert = [
            'name' => $request->name,
            'code' => AcademicYear::generateRandomCode(),
            'start_year' => $request->start_year,
            'end_year' => $request->end_year,
            'status' => $request->status,
            'created_user_id' => $request->created_user_id, 
            'modified_user_id' => $request->modified_user_id, 
            'created_at' => now(),
        ];

        $item = $this->academicYearRepository->create($dataInsert);

        if ($item) {
            return response()->json([
                'success' => true,
                'message' => 'Thêm thành công!',
                'data' => $item,
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Thêm thất bại!',
            ], 500);
        }
    }


public function update(AcademicYearRequest $request, $id)
    {
        $dataUpdate = [
            'name' => $request->name,
            'start_year' => $request->start_year,
            'end_year' => $request->end_year,
            'status' => $request->status,
            'modified_user_id' => $request->modified_user_id, 
            'updated_at' => now(),
        ];

        $item = $this->academicYearRepository->update($id, $dataUpdate);

        if ($item) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thành công!',
                'data' => $item,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật thất bại!',
            ], 500);
        }
    }


    public function delete($id)
    {
        $deletedAcademicYear = $this->academicYearRepository->softDelete($id);
        
        if ($deletedAcademicYear) {
            return response()->json([
                'success' => true,
                'message' => 'Xóa niên khóa thành công!',
                'data' => $deletedAcademicYear,
            ], 200);
        }
    
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy niên khóa hoặc niên khóa vẫn còn hoạt động',
        ], 403);
    }
    

}
