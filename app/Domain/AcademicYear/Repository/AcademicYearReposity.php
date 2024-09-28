<?php
namespace App\Domain\AcademicYear\Repository;
use App\Domain\AcademicYear\Requests\AcademicYearRequest;
use App\Domain\AcademicYear\Models\AcademicYear;
class AcademicYearReposity {
    public function __construct(){

    }

    public function getAcademicYear()
{
    return AcademicYear::where('is_deleted', 0)->get();
}




public function create(array $data)
{
    return AcademicYear::create($data);
}



public function findById(int $id)
{
    return AcademicYear::find($id);
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


public function softDelete(int $id)
{
    $academicYear = AcademicYear::find($id);

    
    if ($academicYear && ($academicYear->status == 1 || $academicYear->status == 2)) {
        $academicYear->is_deleted = 0; 
        $academicYear->save();
        
        return $academicYear;
    }

    return null;
}

}