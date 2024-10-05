<?php

namespace App\Domain\Student\Repository;

use App\Domain\Student\Requests\StudentUpdateRequest;
use App\Models\Student as ModelsStudent;
use App\Models\StudentClassHistory;
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
    
    
        // Lưu đối tượng học sinh
        if ($item->save()) {
            // Cập nhật class_id trong bảng student_class_history
            $studentClassHistory = StudentClassHistory::where('student_id', $id)->first();
            
            if ($studentClassHistory) {
                // Cập nhật class_id nếu record đã tồn tại
                $studentClassHistory->class_id = $request->class_id;
                $studentClassHistory->modified_user_id = $user_id;
            } else {
                // Tạo mới nếu không có record
                $studentClassHistory = new StudentClassHistory();
                $studentClassHistory->student_id = $item->id;
                $studentClassHistory->class_id = $request->class_id;
                $studentClassHistory->start_date = now(); // Gán ngày bắt đầu là thời điểm hiện tại
                $studentClassHistory->status = 1; 
                $studentClassHistory->is_deleted = 1; 
                $studentClassHistory->modified_user_id = $user_id;
            }

            // Lưu đối tượng class history
            if ($studentClassHistory->save()) {
                return true;
            }
        }

    
        return false;
    }
    


}