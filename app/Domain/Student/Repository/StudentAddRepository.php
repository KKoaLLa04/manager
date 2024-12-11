<?php

namespace App\Domain\Student\Repository;

use App\Common\Enums\StatusClassStudentEnum;
use App\Domain\Student\Requests\StudentRequest;
use App\Models\Student as ClassModel;
use App\Models\Student as ModelsStudent;
use App\Models\StudentClassHistory;
use Illuminate\Support\Facades\DB;

class StudentAddRepository {

    public function handle(int $user_id, StudentRequest $request) 
    {
        $request->validated();
    
        DB::beginTransaction();
    
        try {
            $student = new ModelsStudent();
    
            $student->fullname = $request->fullname;
            $student->address = $request->address; 
            $student->dob = $request->dob; 
            $student->status = $request->status; 
            $student->gender = $request->gender; 
            $student->is_deleted = $request->is_deleted ?? 0; 
            $student->created_user_id = $user_id; 
    
            if (!$student->save()) {
                DB::rollBack();
                return false; 
            }
    
            if ($student->status == StatusClassStudentEnum::STUDYING->value) { // Nếu status = 1, cho phép chọn lớp
                $class_id = $request->class_id;
    
                // Thêm lịch sử lớp học vào bảng student_class_history
                $studentClassHistory = new StudentClassHistory();
                $studentClassHistory->student_id = $student->id;
                $studentClassHistory->class_id = $class_id;
                $studentClassHistory->start_date = now(); 
                $studentClassHistory->end_date = null; 
                $studentClassHistory->status = StatusClassStudentEnum::STUDYING->value; 
                $studentClassHistory->is_deleted = 0; 
                $studentClassHistory->created_user_id = $user_id; 
    
                // Lưu lịch sử lớp học
                if (!$studentClassHistory->save()) {
                    DB::rollBack(); 
                    return false; 
                }
            } elseif ($student->status == StatusClassStudentEnum::NOT_YET_CLASS->value) {
                $studentClassHistory = new StudentClassHistory();
                $studentClassHistory->student_id = $student->id;
                $studentClassHistory->class_id = null; // Không chọn lớp
                $studentClassHistory->start_date = now(); 
                $studentClassHistory->end_date = null; 
                $studentClassHistory->status = StatusClassStudentEnum::NOT_YET_CLASS->value; 
                $studentClassHistory->is_deleted = 0; 
                $studentClassHistory->created_user_id = $user_id;
    
                if (!$studentClassHistory->save()) {
                    DB::rollBack();
                    return false;
                }
            }
    
            DB::commit();
            return true;
    
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }
    
}
