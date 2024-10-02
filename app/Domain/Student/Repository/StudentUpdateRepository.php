<?php

namespace App\Domain\Student\Repository;

use App\Domain\Student\Requests\StudentUpdateRequest;
use App\Models\Student as ModelsStudent;
use SebastianBergmann\Type\TrueType;

class StudentUpdateRepository {


    public function handle( int $id, int $user_id, StudentUpdateRequest $request) 
    {
        $request->validated();
    
        $item = ModelsStudent::find($id);
    
        $item->fullname = $request->fullname;
        $item->address = $request->address; 
        $item->dob = $request->dob; 
        $item->status = $request->status; 
        $item->gender = $request->gender; 
        $item->modified_user_id = $user_id; 
    
        // Lưu đối tượng và trả về kết quả
        if ($item->save()) {
            return true;
        }
    
        return false;
    }
    


}