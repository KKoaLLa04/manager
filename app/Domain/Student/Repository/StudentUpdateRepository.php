<?php

namespace App\Domain\Student\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusClassStudentEnum;
use App\Domain\Student\Requests\StudentUpdateRequest;
use App\Models\Student as ModelsStudent;
use App\Models\StudentClassHistory;
use SebastianBergmann\Type\TrueType;

class StudentUpdateRepository {

    public function handle(int $id, int $user_id, StudentUpdateRequest $request)
    {
        $request->validated();
        
        // Tìm học sinh với ID, sử dụng findOrFail để tự động trả về lỗi nếu không tìm thấy học sinh
        $item = ModelsStudent::findOrFail($id);
        
        // Cập nhật thông tin học sinh
        $item->fullname = $request->fullname;
        $item->address = $request->address;
        $item->dob = $request->dob;
        $item->gender = $request->gender;
        $item->status = $request->status;
        $item->modified_user_id = $user_id;
    
        $classId = $request->class_id;
        $status = $request->status;
        
        $currentClassHistory = StudentClassHistory::where('student_id', $id)
            ->where('is_deleted', DeleteEnum::NOT_DELETE) 
            ->orderBy('start_date', 'desc')              
            ->orderBy('updated_at', 'desc')              
            ->orderBy('id', 'desc')                      
            ->first();
    
        if ($status == StatusClassStudentEnum::NOT_YET_CLASS->value && $currentClassHistory) {
            throw new \Exception('Không thể chuyển sang trạng thái "Chưa vào lớp" khi học sinh đã có lớp học.');
        }
        
        $item->save();
    
        // Kiểm tra nếu có sự thay đổi về `class_id` hoặc `status`
        $isClassChanged = $currentClassHistory && $currentClassHistory->class_id != $classId;
        $isStatusChanged = $currentClassHistory && $currentClassHistory->status != $status;
    
        // Nếu chưa có bản ghi lớp học thì tạo mới
        if (!$currentClassHistory) {
            StudentClassHistory::create([
                'student_id' => $id,
                'class_id' => $classId,
                'start_date' => now(),
                'end_date' => null,
                'status' => $status,
                'is_deleted' => DeleteEnum::NOT_DELETE,
                'created_user_id' => $user_id,
            ]);
        } else {
            // Nếu có thay đổi về class hoặc status
            if ($isClassChanged || $isStatusChanged) {
                if ($currentClassHistory) {
                    // Nếu status là 0 (nghỉ học)
                    if ($status == StatusClassStudentEnum::LEAVE->value) {
                        $currentClassHistory->end_date = now(); 
                        // $currentClassHistory->status = StatusClassStudentEnum::LEAVE->value; 
                        $currentClassHistory->modified_user_id = $user_id; 
                        $currentClassHistory->save(); 
                    }
                    elseif ($status == StatusClassStudentEnum::STUDYING->value) {
                        $currentClassHistory->end_date = now(); 
                        // $currentClassHistory->status = StatusClassStudentEnum::LEAVE->value; 
                        $currentClassHistory->modified_user_id = $user_id; 
                        $currentClassHistory->save(); 
    
                        StudentClassHistory::create([
                            'student_id' => $id,
                            'class_id' => $classId,
                            'start_date' => now(),
                            'end_date' => null,
                            'status' => $status,
                            'is_deleted' => DeleteEnum::NOT_DELETE,
                            'created_user_id' => $user_id,
                        ]);
                    }
                }
            }
        }
        return true;
    }

}
    