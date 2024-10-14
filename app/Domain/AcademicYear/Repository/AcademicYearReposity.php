<?php
namespace App\Domain\AcademicYear\Repository;

use App\Common\Enums\AcademicTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusAcademicEnum;
use App\Common\Enums\StatusEnum;
use App\Domain\AcademicYear\Requests\AcademicYearRequest;
use App\Domain\AcademicYear\Models\AcademicYear;
class AcademicYearReposity {
    public function __construct(){

    }

    public function getAcademicYear($keyword = null, $pageIndex = 1, $pageSize = 10)
{
    $query = AcademicYear::where('is_deleted', DeleteEnum::NOT_DELETE->value)
        ->with(['classes.grade']);

    // Tìm kiếm theo từ khóa nếu có
    if ($keyword) {
        $query->where('name', 'LIKE', '%' . $keyword . '%')
              ->orWhere('code', 'LIKE', '%' . $keyword . '%');
    }

    // Thực hiện phân trang
    $academicYears = $query->paginate($pageSize, ['*'], 'page', $pageIndex);

    // Ánh xạ dữ liệu và chuyển `gradeName` từ mảng thành chuỗi
    $mappedData = $academicYears->getCollection()->map(function ($academicYear) {
        return [
            'id' => $academicYear->id,
            'name' => $academicYear->name,
            'code' => $academicYear->code,
            'status' => $academicYear->status,
            'start_year' => strtotime($academicYear->start_year),
            'end_year' => strtotime($academicYear->end_year),
            // Sử dụng implode để chuyển thành chuỗi
            'gradeName' => $academicYear->classes->pluck('grade.name')->unique()->implode(', ')
        ];
    });

    // Thay đổi collection thành một đối tượng mới với dữ liệu đã ánh xạ
    return [
        'data' => $mappedData,
        'total' => $academicYears->total(),
        'current_page' => $academicYears->currentPage(),
        'last_page' => $academicYears->lastPage(),
        'per_page' => $academicYears->perPage(),
    ];
}


public function create(array $data)
{
    return AcademicYear::create($data);
}



public function findById(int $id)
{
    return AcademicYear::where('is_deleted',DeleteEnum::NOT_DELETE->value)->find($id);
}


public function update(int $id, array $data)
{
    $academicYear = AcademicYear::find($id);

    if (!$academicYear) {
        return response()->json([
            'success' => false,
            'message' => 'Niên khóa không tôn tại',
        ], 400);
    }

    if($academicYear->status == AcademicTypeEnum::FINISHED->value){
        return response()->json([
            'success' => false,
            'message' => 'Niên khóa đã đóng',
        ], 400);
    }

    if($academicYear->status == AcademicTypeEnum::NOT_STARTED_YET->value || $academicYear->status == AcademicTypeEnum::ONGOING->value){
    $academicYear->fill($data);
    $academicYear->save();
    }
    return $academicYear;
}


public function softDelete(int $id, int $user_id)
{
   
    $academicYear = AcademicYear::find($id);

    if(!$academicYear){
        return response()->json([
            'success' => false,
            'message' => 'Niên khóa không tôn tại',
        ], 400);
    }
    
    if ($academicYear && ($academicYear->status == AcademicTypeEnum::NOT_STARTED_YET->value)){
        
        $academicYear->is_deleted = DeleteEnum::DELETED->value;
        
        $academicYear->modified_user_id = $user_id;
        
        $academicYear->save();
        
        return $academicYear; 
    }

    return null; 
}


}