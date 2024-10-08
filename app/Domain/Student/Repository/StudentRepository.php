<?php
namespace App\Domain\Student\Repository;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class StudentRepository {


    public function handle () {

        $students = Student::select('id', 'fullname','address','student_code','dob','status','gender','created_user_id','modified_user_id','created_at','updated_at',)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->get();

        if($students->count() > 0){
            return $students;
        }

        return [];

    }

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
    
    // Phương thức gán phụ huynh cho học sinh
    public function assignParentToStudent(int $student_id, int $parent_id)
    {
        // Kiểm tra phụ huynh có hợp lệ không
        $parent = User::where('id', $parent_id)
            ->where('access_type', AccessTypeEnum::GUARDIAN->value)
            ->where('is_deleted', DeleteEnum::DELETED->value) 
            ->first();
    
        if (!$parent) {
            return [
                'error' => 'Phụ huynh không hợp lệ hoặc không có quyền truy cập',
            ];
        }
    
        // Kiểm tra học sinh có tồn tại không
        $student = Student::find($student_id);
        if (!$student) {
            return [
                'error' => 'Học sinh không tồn tại',
            ];
        }
          // Kiểm tra xem học sinh đã có phụ huynh được gán chưa
        if ($student->parents()->exists()) {
            return [
                'error' => 'Học sinh này đã được gán phụ huynh, không thể gán lại.',
            ];
        }
    
        // Gán phụ huynh cho học sinh
        $student->parents()->attach($parent->id, ['created_user_id' => Auth::user()->id]);
        $childrenCount = $parent->students()->count();

        return [
            'student' => $student,
            'parent' => $parent,
            'children_count' => $childrenCount
        ];
    }
    
    public function detachParentFromStudent(int $student_id, int $parent_id)
    {
        // Kiểm tra học sinh có tồn tại không
        $student = Student::find($student_id);
        if (!$student) {
            return null; // Hoặc ném ra exception nếu muốn
        }
    
        // Kiểm tra phụ huynh có tồn tại và hợp lệ không
        $parent = User::where('id', $parent_id)
            ->where('access_type', AccessTypeEnum::GUARDIAN->value)
            ->where('is_deleted', DeleteEnum::DELETED->value)
            ->first(); 
    
        if (!$parent) {
            return [
                'error' => 'Phụ huynh không hợp lệ hoặc không có quyền truy cập',
            ];
        }
    
        $relationshipExists = $student->parents()->where('users.id', $parent_id)->exists();
        if (!$relationshipExists) {
            return [
                'error' => 'Phụ huynh không hợp lệ hoặc không được gán cho học sinh này',
            ];
        }
    
        $student->parents()->detach($parent_id);
        $childrenCount = $parent->students()->count();
    
        return [
            'student' => $student,
            'parent_id' => $parent_id, // Đảm bảo rằng parent_id luôn được trả về
            'children_count' => $childrenCount, // Trả về số lượng con còn lại của phụ huynh
        ];
    }
    


    
    


}