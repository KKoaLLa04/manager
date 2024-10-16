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

        $students = Student::select('id', 'fullname','address','student_code','dob','phone','status','gender','created_user_id','modified_user_id','created_at','updated_at',)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->get();

        if($students->count() > 0){
            return $students;
        }

        return [];

    }

    public function paginateStudents($pageSize)
    {
        // Lấy danh sách sinh viên không bị xóa
        $students = Student::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                            ->paginate($pageSize);
    
        // Lấy tất cả lớp và chuyển đổi thành mảng với key là id
        $classes = ClassModel::with('academicYear')->get()->keyBy('id'); // Lấy thông tin lớp cùng với thông tin niên khóa
    
        // Sử dụng map để lấy dữ liệu và thêm thông tin lớp
        $students->transform(function ($student) use ($classes) {
            // Lấy thông tin lớp học gần nhất (hoặc theo cách bạn muốn)
            $classHistory = $student->classHistory->first(); // Lấy lớp học đầu tiên
    
            $classId = optional($classHistory)->class_id; // Lấy class_id từ lớp học
            $class = $classes->get($classId); // Lấy thông tin lớp từ mảng đã tạo
    
            return [
                'id' => $student->id,
                'student_code' => $student->student_code,
                'fullname' => $student->fullname,
                // 'address' => $student->address,
                // 'dob' => $student->dob ? strtotime($student->dob) : null,
                'status' => $student->status,
                'phone' => $student->phone,
                'gender' => $student->gender,
                // 'created_at' => $student->created_at ? strtotime($student->created_at) : null,
                // 'updated_at' => $student->updated_at ? strtotime($student->updated_at) : null,
                'class_id' => $classId,
                'class_name' => $class->name ?? null, 
                'academic_year_name' => $class->academicYear->name ?? null, // Lấy tên niên khóa
            ];
        });
    
        return $students; 
    }
    

    
    // Phương thức gán phụ huynh cho học sinh
    public function assignParentToStudent(int $student_id, int $parent_id)
    {
        // Kiểm tra phụ huynh có hợp lệ không
        $parent = User::where('id', $parent_id)
            ->where('access_type', AccessTypeEnum::GUARDIAN->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value) 
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

            // Ẩn các trường không cần thiết từ student và parent
        $student->makeHidden(['is_deleted', 'created_user_id', 'modified_user_id', 'created_at', 'updated_at']);
        $parent->makeHidden(['is_deleted', 'created_user_id', 'modified_user_id', 'created_at', 'updated_at','email_verified_at','access_type']);

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
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
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
    

    public function getStudentWithDetails($id)
    {
        // Kiểm tra xem học sinh có bị xóa không
        $student = Student::with(['classHistory' => function($query) {
            $query->select('student_id', 'class_id', 'start_date', 'end_date', 'status')
                  ->with(['class' => function($q) {
                      $q->select('id', 'name', 'academic_year_id') // Thêm 'academic_year_id' nếu cần
                        ->with('academicYear:id,name'); // Gọi tới quan hệ academicYear
                  }]);
        }, 'parents' => function($query) {
            $query->select('users.id', 'fullname', 'username', 'phone', 'code', 'gender', 'email', 'dob')
                  ->where('users.access_type', AccessTypeEnum::GUARDIAN->value)
                  ->where('users.is_deleted', DeleteEnum::NOT_DELETE->value);
        }])
        ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
        ->find($id);
    
        // Kiểm tra nếu không tìm thấy học sinh
        if (!$student) {
            return null; // hoặc ném ra ngoại lệ tùy theo yêu cầu của bạn
        }
    
        // Ẩn các trường không mong muốn
        $student->makeHidden(['is_deleted', 'created_user_id', 'modified_user_id', 'created_at', 'updated_at']);
      
        // Chuyển đổi `dob` của học sinh sang timestamp
        $student->dob = strtotime($student->dob);
    
        // Xử lý class history, chuyển đổi `start_date` và `end_date` sang timestamp
        $student->classHistory->map(function($history) {
            $history->start_date = strtotime($history->start_date);
            $history->end_date = $history->end_date ? strtotime($history->end_date) : null;
            return $history;
        });
    
        // Xử lý `dob` cho các phụ huynh
        $student->parents->map(function($parent) {
            $parent->dob = strtotime($parent->dob);
            return $parent;
        });
    
        // Tìm lớp học hiện tại (lớp có `end_date` là null)
        $currentClass = $student->classHistory->firstWhere('end_date', null);
        
        if ($currentClass) {
            $student->current_class_name = optional($currentClass->class)->name;
            $student->current_academic_year_name = optional($currentClass->class->academicYear)->name; // Lấy tên academic_year
        } else {
            $student->current_class_name = null;
            $student->current_academic_year_name = null;
        }
    
        return $student; // Trả về đối tượng student đã được xử lý
    }
    
    
 
    
    public function getAllParentsWithChildrenCount($pageSize)
    {
        $parents = User::where('access_type', AccessTypeEnum::GUARDIAN->value)
                        ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                        ->with(['students' => function($query) {
                            $query->select('students.id', 'student_code', 'fullname', 'gender', 'dob')
                                ->with(['classHistory' => function($classQuery) {
                                    $classQuery->where('status', 1)
                                                ->where('is_deleted', 0)
                                                ->whereNull('end_date') // Chỉ lấy lịch sử lớp học chưa kết thúc
                                                ->with(['class' => function($class) {
                                                    $class->select('id', 'name', 'academic_year_id') // Lấy thông tin lớp
                                                        ->with(['academicYear' => function($yearQuery) {
                                                            $yearQuery->select('id', 'name'); // Lấy thông tin niên khóa
                                                        }]);
                                                }]);
                                }]);
                        }])
                        ->withCount('students') // Đếm số lượng học sinh
                        ->paginate($pageSize); 
       
        $parents->getCollection()->transform(function($parent) {
            // Lấy danh sách học sinh và thông tin lớp và năm học
            $students = $parent->students->map(function($student) {
                // Lấy lớp học gần nhất từ lịch sử lớp học
                $classHistory = $student->classHistory->first(); // Giả định rằng lớp học đầu tiên là lớp hiện tại
                $class = optional($classHistory)->class; // Lấy lớp từ lịch sử lớp học

                return [
                    'fullname' => $student->fullname,
                    'dob' => $student->dob ? strtotime($student->dob) : null,
                    'class_name' => $class ? $class->name : null,
                    'academic_year_name' => $class && $class->academicYear ? $class->academicYear->name : null, 
                ];
            });

            return [
                'id' => $parent->id,
                'code' => $parent->code,
                'fullname' => $parent->fullname,
                'email' => $parent->email,
                'gender' => $parent->gender,
                'dob' => $parent->dob ? strtotime($parent->dob) : null,
                'phone' => $parent->phone,
                'username' => $parent->username,
                'children_count' => $parent->students_count, 
                'children_info' => $students, 
            ];
        });

        return $parents; 
    }



    
    

    
    
    
    

    
    

    
    


}