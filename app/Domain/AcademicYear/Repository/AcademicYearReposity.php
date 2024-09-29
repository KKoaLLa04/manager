<?php
namespace App\Domain\AcademicYear\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusAcademicEnum;
use App\Common\Enums\StatusEnum;
use App\Domain\AcademicYear\Requests\AcademicYearRequest;
use App\Domain\AcademicYear\Models\AcademicYear;
class AcademicYearReposity {
    public function __construct(){

    }

    public function getAcademicYear()
{
    return AcademicYear::where('is_deleted', DeleteEnum::NOT_DELETE->value)->get();
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
        return null;
    }

    
    $academicYear->fill($data);
    $academicYear->save();

    return $academicYear;
}


public function softDelete(int $id, int $user_id)
{
   
    $academicYear = AcademicYear::find($id);

    
    if ($academicYear && ($academicYear->status == 0 || $academicYear->status == 2)) {
        
        $academicYear->is_deleted = DeleteEnum::DELETED->value;
        
        $academicYear->modified_user_id = $user_id;
        
        $academicYear->save();
        
        return $academicYear; 
    }

    return null; 
}


}