<?php

namespace App\Domain\Student\Repository;

use App\Domain\Student\Requests\StudentRequest;
use App\Models\Student as ModelsStudent;
use SebastianBergmann\Type\TrueType;

class StudentAddRepository {


    public function handle(int $user_id, StudentRequest $request) 
    {
        // Xác thực các yêu cầu
        $request->validated();
    
        // Tạo mới một đối tượng Student
        $item = new ModelsStudent();
    
        // Gán các giá trị từ request vào đối tượng
        $item->fullname = $request->fullname;
        $item->address = $request->address; // Thêm trường địa chỉ
        $item->student_code = $request->student_code; // Thêm trường mã học sinh
        $item->dob = $request->dob; // Thêm trường ngày sinh
        $item->status = $request->status; // Trạng thái
        $item->gender = $request->gender; // Giới tính
        $item->is_deleted = $request->is_deleted ?? 0; // Trường xóa (mặc định là 0 nếu không có giá trị)
        $item->created_user_id = $user_id; // ID người dùng tạo
    
        // Lưu đối tượng và trả về kết quả
        if ($item->save()) {
            return true;
        }
    
        return false;
    }
    


}