<?php
namespace App\Domain\School\Requests\SchoolRequest;



?>

<?php

namespace App\Domain\School\Repository;

use App\Domain\School\Models\School;

class SchoolRepository
{
    // Lấy tất cả các trường học
    public function getAllSchools()
    {
        return School::all();
    }
     
     public function updateSchool($id, array $data)
     {
         $school = School::findOrFail($id);
         $school->update($data);
          return $school;
     }
     
     public function findSchoolById($id)
    {
        return School::find($id); 
    }
}