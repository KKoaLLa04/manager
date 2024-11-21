<?php
namespace App\Domain\RollcallStatistics\Repository;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusStudentEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\RollCallHistory\Models\RollCallHistory;
use App\Models\Classes;
use App\Models\StudentClassHistory;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class RollcallStatisticsRepository {


    public function listClasses($pageSize, $keyWord = null)
    {    
        $query = Classes::where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->with(['grade', 'classHistory' => function ($query) {
                $query->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                      ->where('status', StatusEnum::ACTIVE->value);
            }, 'classSubjectTeacher.user' => function ($query) {
                $query->select('id', 'fullname', 'email');
            }]);
    
        if ($keyWord) {
            $query->where(function($q) use ($keyWord) {
                $q->where('name', 'LIKE', '%' . $keyWord . '%')
                  ->orWhereHas('grade', function ($q) use ($keyWord) {
                      $q->where('name', 'LIKE', '%' . $keyWord . '%');
                  })
                  ->orWhereHas('classSubjectTeacher.user', function ($q) use ($keyWord) {
                      $q->where('fullname', 'LIKE', '%' . $keyWord . '%');
                  });
            });
        }
    
        // dd($query->toSql(), $query->getBindings()); 
    
        $classes = $query->paginate($pageSize);
    
        return [
            'total' => $classes->total(),
            'data' => $classes->map(function ($class) {
                $mainTeacher = $class->classSubjectTeacher
                    ->where('access_type', StatusTeacherEnum::MAIN_TEACHER->value)
                    ->first()->user ?? null;
    
                return [
                    // 'class_id' => $class->id,
                    'class_name' => $class->name ?? null,
                    'grade_name' => $class->grade->name ?? null,
                    'status_class' => $class->status ?? null,
                    'teacher_name' => $mainTeacher ? $mainTeacher->fullname : 'Chưa có giáo viên chủ nhiệm',
                    // 'teacher_email' => $mainTeacher ? $mainTeacher->email : null,
                    'total_students' => $class->classHistory->count(),
                ];
            }),
            'current_page' => $classes->currentPage(),
            'per_page' => $classes->perPage(),
        ];
    }

    public function getClassRollCall($classId, $pageSize)
    {
        // Lấy danh sách học sinh chưa nghỉ học và thuộc lớp
        $students = StudentClassHistory::where('class_id', $classId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where(function ($query) {
                $query->whereNull('end_date') // Học sinh chưa có ngày nghỉ học
                      ->orWhere('end_date', '>', now()); // Hoặc nghỉ học sau ngày hiện tại
            })
            ->with(['student' => function ($query) {
                $query->select('id', 'fullname', 'student_code'); // Chỉ lấy các cột cần thiết
            }])
            ->get()
            ->unique('student_id'); // Loại bỏ bản ghi trùng lặp theo `student_id`
    
        // Format dữ liệu
        $data = $students->map(function ($studentClassHistory) {
            $student = $studentClassHistory->student;
            return [
                'student_name' => $student->fullname,
                'student_code' => $student->student_code,
            ];
        })->values(); // Đảm bảo trả về một mảng không có key
    
        // Tính tổng số học sinh
        $totalStudents = $students->count();
    
        // Phân trang
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginatedData = collect($data)->slice(($currentPage - 1) * $pageSize, $pageSize)->values();
        $paginator = new LengthAwarePaginator(
            $paginatedData,
            $totalStudents,
            $pageSize,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
    
        // Trả về kết quả
        return [
            'message' => 'Lấy lịch sử điểm danh thành công',
            'status' => 'success',
            'total_students' => $totalStudents,
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'pageIndex' => $paginator->currentPage(),
            'pageSize' => $paginator->perPage(),
        ];
    }
    
    

    
}