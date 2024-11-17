<?php

namespace App\Domain\TeacherStudent\Repository;

use App\Domain\TeacherStudent\Requests\TeacherStudentRequest;
use App\Models\Student as ClassModel;
use App\Models\Student as ModelsStudent;
use App\Models\StudentClassHistory;
use Illuminate\Support\Facades\DB;

class TeacherStudentAddRepository {

    public function handle(int $user_id, TeacherStudentRequest $request)
    {
        $request->validated();

        DB::beginTransaction();


            $student = new ModelsStudent();

            $student->fullname = $request->fullname;
            $student->address = $request->address;
            // $student->phone = $request->phone; //comment
            $student->dob = $request->dob;
            $student->status = $request->status;
            $student->gender = $request->gender;
            $student->is_deleted = $request->is_deleted ?? 0;
            $student->created_user_id = $user_id;

            if (!$student->save()) {
                DB::rollBack();
                return false;
            }
            $class_id = $request->class_id;

            // Thêm lịch sử lớp học vào bảng student_class_history
            $studentClassHistory = new StudentClassHistory();
            $studentClassHistory->student_id = $student->id; // Lưu ID của học sinh vừa tạo
            $studentClassHistory->class_id = $class_id; // Lưu ID của lớp
            $studentClassHistory->start_date = now();
            $studentClassHistory->end_date = null;
            $studentClassHistory->status = 1; // 1: Đang học
            $studentClassHistory->is_deleted = 0; // 0: Active
            $studentClassHistory->created_user_id = $user_id;


            // Lưu lịch sử lớp học
            if (!$studentClassHistory->save()) {
                DB::rollBack(); // Hủy transaction nếu lưu không thành công
                return false; // Trả về false nếu lưu không thành công
            }

            DB::commit();
            return true;


    }
}
