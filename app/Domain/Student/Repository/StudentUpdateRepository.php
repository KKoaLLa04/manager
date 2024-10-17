<?php

namespace App\Domain\Student\Repository;

use App\Domain\Student\Requests\StudentUpdateRequest;
use App\Models\Student as ModelsStudent;
use App\Models\StudentClassHistory;
use SebastianBergmann\Type\TrueType;

class StudentUpdateRepository {


    // public function handle(int $id, int $user_id, StudentUpdateRequest $request) 
    // {
    //     $request->validated();
    
    //     $item = ModelsStudent::find($id);
    
    //     // Cập nhật thông tin học sinh
    //     $item->fullname = $request->fullname;
    //     $item->address = $request->address; 
    //     $item->dob = $request->dob; 
    //     $item->status = $request->status; 
    //     $item->gender = $request->gender; 
    //     $item->modified_user_id = $user_id; 
    
    //     // Lưu đối tượng học sinh
    //     if ($item->save()) {
    //         // Cập nhật class_id trong bảng student_class_history
    //         $studentClassHistory = StudentClassHistory::where('student_id', $id)->orderBy('start_date', 'desc')->first();
    
    //         if ($studentClassHistory) {
    //             // Cập nhật end_date với thời điểm hiện tại trước khi thay đổi lớp
    //             $studentClassHistory->end_date = now(); // Cập nhật ngày kết thúc
    //             $studentClassHistory->modified_user_id = $user_id;
    //             $studentClassHistory->save(); // Lưu thay đổi
                
    //             // Tạo một bản ghi mới cho lớp học mới
    //             $newStudentClassHistory = new StudentClassHistory();
    //             $newStudentClassHistory->student_id = $item->id;
    //             $newStudentClassHistory->class_id = $request->class_id;
    //             $newStudentClassHistory->start_date = now(); // Gán ngày bắt đầu là thời điểm hiện tại
    //             $newStudentClassHistory->end_date = null; // Gán ngày kết thúc là null cho lớp hiện tại
    //             $newStudentClassHistory->status = 1; 
    //             $newStudentClassHistory->is_deleted = 0; 
    //             $newStudentClassHistory->created_user_id = $user_id; // Ghi lại người tạo
    
    //             // Lưu đối tượng class history mới
    //             $newStudentClassHistory->save();
    //         } else {
    //             // Nếu không có record, tạo mới lịch sử lớp học
    //             $studentClassHistory = new StudentClassHistory();
    //             $studentClassHistory->student_id = $item->id;
    //             $studentClassHistory->class_id = $request->class_id;
    //             $studentClassHistory->start_date = now(); // Gán ngày bắt đầu là thời điểm hiện tại
    //             $studentClassHistory->end_date = null; // Gán ngày kết thúc là null cho lớp hiện tại
    //             $studentClassHistory->status = 1; 
    //             $studentClassHistory->is_deleted = 0; 
    //             $studentClassHistory->created_user_id = $user_id; // Ghi lại người tạo
    
    //             // Lưu đối tượng class history
    //             $studentClassHistory->save();
    //         }
    
    //         return true;
    //     }
    
    //     return false;
    // }
    
    public function handle(int $id, int $user_id, StudentUpdateRequest $request) 
{
    $request->validated();
    
    // Tìm học sinh với ID
    $item = ModelsStudent::find($id);
    
    // Cập nhật thông tin học sinh
    $item->fullname = $request->fullname;
    $item->address = $request->address; 
    $item->dob = $request->dob; 
    // $item->dob = $request->phone;
    $item->status = $request->status; 
    $item->gender = $request->gender; 
    $item->modified_user_id = $user_id; 
    
    // Lưu đối tượng học sinh
    if ($item->save()) {
        // Lấy class_id hiện tại từ lịch sử lớp học gần nhất
        $studentClassHistory = StudentClassHistory::where('student_id', $id)
                                                  ->orderBy('start_date', 'desc')
                                                  ->first();
        $currentClassId = $studentClassHistory ? $studentClassHistory->class_id : null;

        // Kiểm tra nếu có sự thay đổi class_id
        if ($request->class_id && $request->class_id != $currentClassId) {
            if ($studentClassHistory) {
                // Cập nhật end_date cho lịch sử lớp hiện tại
                $studentClassHistory->end_date = now(); 
                $studentClassHistory->modified_user_id = $user_id;
                $studentClassHistory->save(); 
            }

            // Tạo một bản ghi mới cho lớp học mới
            $newStudentClassHistory = new StudentClassHistory();
            $newStudentClassHistory->student_id = $item->id;
            $newStudentClassHistory->class_id = $request->class_id;
            $newStudentClassHistory->start_date = now(); // Gán ngày bắt đầu
            $newStudentClassHistory->end_date = null; // Lớp hiện tại chưa kết thúc
            $newStudentClassHistory->status = 1; 
            $newStudentClassHistory->is_deleted = 0; 
            $newStudentClassHistory->created_user_id = $user_id; // Ghi lại người tạo

            // Lưu đối tượng class history mới
            $newStudentClassHistory->save();
        }

        return true;
    }

    return false;
}



}