<?php
namespace App\Domain\TeacherStudent\Repository;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Models\Classes;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\StudentClassHistory;
use App\Models\User;
use App\Models\UserStudent;
use Illuminate\Support\Facades\Auth;
use SebastianBergmann\Type\StaticType;

class TeacherStudentRepository {


    public function handle () {

        $students = Student::select('id', 'fullname','address','student_code','dob','phone','status','gender','created_user_id','modified_user_id','created_at','updated_at',)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->get();

        if($students->count() > 0){
            return $students;
        }

        return [];

    }

    public function paginateStudents($pageIndex,$pageSize, $class_id = 0, $keyWord = null)
    {
        $classFind = Classes::find($class_id);

        if ($classFind) {
            $studentHistory = StudentClassHistory::where('class_id', $classFind->id)
                ->where('status', StatusEnum::ACTIVE->value)
                ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->get();

            $arrStudentId = $studentHistory->map(function ($item) {
                return $item->student_id;
            });
        } else {
            return Student::where('id', 0)->get(); // Trả về danh sách rỗng nếu không tìm thấy lớp
        }

        // Lọc danh sách sinh viên theo từ khóa nếu có
        $studentsQuery = Student::whereIn('id', $arrStudentId->toArray())
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value);

        if ($keyWord) {
            $studentsQuery->where(function ($query) use ($keyWord) {
                $query->where('student_code', 'LIKE', '%' . $keyWord . '%')
                    ->orWhere('fullname', 'LIKE', '%' . $keyWord . '%')
                    ->orWhere('address', 'LIKE', '%' . $keyWord . '%');
            });
        }

        $students = $studentsQuery->paginate($pageSize, ['*'], 'page', $pageIndex);

        // Lấy tất cả lớp và chuyển đổi thành mảng với key là id
        $classes = ClassModel::with('academicYear')->get()->keyBy('id');

        // Sử dụng map để thêm thông tin lớp và cha mẹ
        $students->transform(function ($student) use ($classes) {
            $classHistory = $student->classHistory->first();
            $classId = optional($classHistory)->class_id;
            $class = $classes->get($classId);

            $parent = null;

            $parent = $student->parents->first();

            return [
                'id' => $student->id,
                'student_code' => $student->student_code,
                'fullname' => $student->fullname,
                'address' => $student->address,
                'dob' => $student->dob ? strtotime($student->dob) : null,
                'status' => $student->status,
                'gender' => $student->gender,
                'class_id' => $classId,
                'class_name' => $class->name ?? null,
                'academic_year_name' => $class->academicYear->name ?? null,
                'parent_name' => $parent ? $parent->fullname : "",
                'parent_phone' => $parent ? $parent->phone : "",
                'parent_code' => $parent ? $parent->code : "",
                'parent_status' => $parent ? $parent->status : "",
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
            $query->select('users.id', 'fullname', 'username', 'phone', 'code', 'gender', 'email', 'dob', 'status')
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



    public function getAllParentsWithChildrenCount($keyWord = null, $pageIndex = 1, $pageSize = 15)
    {
        $parentsQuery = User::where('access_type', AccessTypeEnum::GUARDIAN->value)
                            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                            ->with(['students' => function($query) {
                                $query->select('students.id', 'student_code', 'fullname', 'gender', 'dob')
                                    ->with(['classHistory' => function($classQuery) {
                                        $classQuery->where('status', 1)
                                                   ->where('is_deleted', 0)
                                                   ->whereNull('end_date')
                                                   ->with(['class' => function($class) {
                                                       $class->select('id', 'name', 'academic_year_id')
                                                             ->with(['academicYear' => function($yearQuery) {
                                                                 $yearQuery->select('id', 'name');
                                                             }]);
                                                   }]);
                                    }]);
                            }])
                            ->withCount('students'); // Đếm số lượng học sinh

        // Áp dụng tìm kiếm nếu có keyword
        if ($keyWord) {
            $parentsQuery->where(function($query) use ($keyWord) {
                $query->where('fullname', 'like', "%$keyWord%")
                      ->orWhere('code', 'like', "%$keyWord%")
                      ->orWhere('email', 'like', "%$keyWord%")
                      ->orWhere('phone', 'like', "%$keyWord%");
            });
        }

        // Phân trang
        $parents = $parentsQuery->paginate($pageSize, ['*'], 'page', $pageIndex);

        // Format dữ liệu
        $parents->getCollection()->transform(function($parent) {
            $students = $parent->students->map(function($student) {
                $classHistory = $student->classHistory->first(); // Lấy lịch sử lớp đầu tiên (giả định là lớp hiện tại)
                $class = optional($classHistory)->class; // Lấy thông tin lớp từ lịch sử lớp

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
