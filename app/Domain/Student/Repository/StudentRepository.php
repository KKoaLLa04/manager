<?php
namespace App\Domain\Student\Repository;

use App\Common\Enums\DeleteEnum;
use App\Models\ClassModel;
use App\Models\Student;

class StudentRepository {


    public function handle () {

        $students = Student::select('id', 'fullname','address','student_code','dob','status','gender','created_user_id','modified_user_id','created_at','updated_at',)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->get();

        if($students->count() > 0){
            return $students;
        }

        return [];

    }

    // public function paginateStudents($pageSize)
    // {
    //     // Lấy danh sách sinh viên cùng với classHistory
    //     $students = Student::with('classHistory')->where('is_deleted', 1)->paginate($pageSize);
    
    //     // Sử dụng map để lấy dữ liệu và thêm class_id 
    //     collect( $students->transform(function ($student) {
    //         return [
    //             'id' => $student->id,
    //             'fullname' => $student->fullname,
    //             'address' => $student->address,
    //             'student_code' => $student->student_code,
    //             'dob' => $student->dob,
    //             'status' => $student->status,
    //             'gender' => $student->gender,
    //             'created_user_id' => $student->created_user_id,
    //             'modified_user_id' => $student->modified_user_id,
    //             'created_at' => $student->created_at,
    //             'updated_at' => $student->updated_at,
    //             'class_id' => optional($student->classHistory)->class_id, // Lấy class_id từ classHistory
    //             'class_name' => optional($student->classHistory->class)->name,
    //             //  'class_name' => optional(optional($student->classHistory)->class)->name,        
    //             // 'academicyear_id' => optional($student->classHistory->class)->academicyear_id, 
    //         ];
    //     }));
    //     return $students; // Trả về đối tượng LengthAwarePaginator
    // }

    public function paginateStudents($pageSize)
    {
        // Lấy danh sách sinh viên không bị xóa
        $students = Student::where('is_deleted', DeleteEnum::DELETED->value)->paginate($pageSize);
    
        // Lấy tất cả lớp và chuyển đổi thành mảng với key là id
        $classes = ClassModel::all()->keyBy('id');
    
        // Sử dụng map để lấy dữ liệu và thêm thông tin lớp
        $students->transform(function ($student) use ($classes) {
            $classId = optional($student->classHistory)->class_id; // Lấy class_id từ classHistory
            $class = $classes->get($classId); // Lấy thông tin lớp từ mảng đã tạo
    
            return [
                'id' => $student->id,
                'fullname' => $student->fullname,
                'address' => $student->address,
                'student_code' => $student->student_code,
                'dob' => $student->dob,
                'status' => $student->status,
                'gender' => $student->gender,
                'created_user_id' => $student->created_user_id,
                'modified_user_id' => $student->modified_user_id,
                'created_at' => $student->created_at,
                'updated_at' => $student->updated_at,
                'class_id' => $classId,
                'class_name' => $class->name ?? null, // Lấy name từ lớp
                'class_details' => $class, // Lấy tất cả thông tin từ lớp
            ];
        });
    
        return $students; // Trả về đối tượng LengthAwarePaginator
    }
    
    
    


}