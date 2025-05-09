<?php
namespace App\Domain\TeacherRollCallHistory\Repository;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\GenderEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusStudentEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\RollCallHistory\Models\RollCallHistory;
use App\Domain\TeacherRollCallHistory\Models\TeacherRollCallHistory;
use App\Models\Classes;
use App\Models\ClassSubjectTeacher;
use App\Models\StudentClassHistory;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;


class TeacherRollCallHistoryRepository{
    public function getClassesWithRollCallHistories($userId, $pageSize, $keyWord = null)
    {
        $query = Classes::where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereHas('classSubjectTeacher', function ($q) use ($userId) {
                $q->where('user_id', $userId) // Lọc các lớp mà giáo viên đó dạy
                  ->where('is_deleted', DeleteEnum::NOT_DELETE->value);
            })
            ->with([
                'grade',
                'classHistory' => function ($query) {
                    $query->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                          ->where('status', StatusEnum::ACTIVE->value)
                          ->with('rollCallHistories');
                },
                'classSubjectTeacher' => function ($query) {
                    $query->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                          ->whereIn('access_type', [
                              StatusTeacherEnum::MAIN_TEACHER->value, 
                              StatusTeacherEnum::TEACHER->value
                          ]);
                },
                'classSubjectTeacher.user' => function ($query) {
                    $query->select('id', 'fullname', 'email');
                },
            ]);
    
        if ($keyWord) {
            $query->where(function ($q) use ($keyWord) {
                $q->where('name', 'LIKE', '%' . $keyWord . '%')
                  ->orWhereHas('grade', function ($q) use ($keyWord) {
                      $q->where('name', 'LIKE', '%' . $keyWord . '%');
                  })
                  ->orWhereHas('classSubjectTeacher.user', function ($q) use ($keyWord) {
                      $q->where('fullname', 'LIKE', '%' . $keyWord . '%');
                  });
            });
        }
    
        $classes = $query->paginate($pageSize);
    
        return [
            'total' => $classes->total(),
            'data' => $classes->map(function ($class) {
                // Lấy thông tin giáo viên chủ nhiệm và giáo viên bộ môn
                $mainTeacher = $class->classSubjectTeacher
                    ->where('access_type', StatusTeacherEnum::MAIN_TEACHER->value)
                    ->first()->user ?? null;
    
           
    
                return [
                    'class_id' => $class->id,
                    'class_name' => $class->name ?? null,
                    'grade_name' => $class->grade->name ?? null,
                    'teacher_name' => $mainTeacher ? $mainTeacher->fullname : 'Chưa có giáo viên chủ nhiệm',
                    'teacher_email' => $mainTeacher ? $mainTeacher->email : null,
                    'total_students' => $class->classHistory->count(),
                ];
            }),
            'current_page' => $classes->currentPage(),
            'per_page' => $classes->perPage(),
        ];
    }
    
    
 
    public function getTeacherClassRollCallHistories($classId, $pageSize, $keyWord = null, $date = null)
    {
        // Tính tổng số học sinh
        $totalStudents = StudentClassHistory::where('class_id', $classId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->count();
    
        // Lấy danh sách lịch sử điểm danh với điều kiện tìm kiếm và lọc theo ngày
        $rollCallHistoriesQuery = RollCallHistory::where('class_id', $classId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->with(['user' => function ($query) {
                $query->select('id', 'fullname', 'email');
            }])
            ->orderBy('date', 'desc');
    
        // Tìm kiếm theo từ khóa
        if ($keyWord) {
            $rollCallHistoriesQuery->whereHas('user', function ($query) use ($keyWord) {
                $query->where('fullname', 'like', '%' . $keyWord . '%')
                    ->orWhere('email', 'like', '%' . $keyWord . '%');
            });
        }
    
        // Lọc theo ngày
        if ($date) {
            $rollCallHistoriesQuery->whereDate('date', $date);
        }
    
        // Lấy dữ liệu và kiểm tra nếu có dữ liệu
        $rollCallHistories = $rollCallHistoriesQuery->get();
        if ($rollCallHistories->isEmpty()) {
            return [
                'message' => 'Không có lịch sử điểm danh',
                'status' => 'success',
                'total_students' => $totalStudents,
                'data' => [],
                'total' => 0,
                'pageIndex' => 1,
                'pageSize' => $pageSize,
            ];
        }
    
        // Nhóm theo ngày (YYYY-MM-DD)
        $groupedHistories = $rollCallHistories->groupBy(function($history) {
            return Carbon::parse($history->date)->toDateString();
        });
    
        // Tạo mảng kết quả chỉ lấy bản ghi đầu tiên của mỗi ngày
        $data = $groupedHistories->map(function ($historiesPerDay) {
            $firstHistory = $historiesPerDay->first();
            $formattedDate = Carbon::parse($firstHistory->date)->translatedFormat('l, d/m/Y');
            $totalPresentStudents = $historiesPerDay->where('status', StatusStudentEnum::PRESENT->value)->count();
    
            return [
                'class_id' => $firstHistory->class_id,
                'date' => $formattedDate,
                'teacherHomeRoomName' => $firstHistory->user ? $firstHistory->user->fullname : 'Unknown',
                'teacherHomeRoomGmail' => $firstHistory->user ? $firstHistory->user->email : 'Unknown',
                'total_Studente_Attendenced' => $totalPresentStudents,
            ];
        })->values();
    
        // Phân trang dữ liệu
        $totalDays = $data->count();
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paginatedData = $data->slice(($currentPage - 1) * $pageSize, $pageSize)->values();
    
        $paginator = new LengthAwarePaginator(
            $paginatedData,
            $totalDays,
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
    

        public function getTeacherClassRollCallHistory($classId, $date) 
        {
            // Tính tổng số học sinh
            $totalStudents = StudentClassHistory::where('class_id', $classId)
                ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->count();
        
            // Lấy danh sách lịch sử điểm danh theo class_id và ngày cụ thể
            $rollCallHistories = RollCallHistory::where('class_id', $classId)
                ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereDate('date', $date)
                ->with(['student' => function ($query) {
                    // Lấy thông tin chi tiết của học sinh
                    $query->select('id', 'student_code', 'fullname', 'dob', 'gender');
                }])
                ->orderBy('time', 'asc') 
                ->get();
        
            // Kiểm tra nếu không có dữ liệu
            if ($rollCallHistories->isEmpty()) {
                return [
                    'message' => 'Không có lịch sử điểm danh cho ngày này',
                    'status' => 'error',
                ];
            }
        
            // Chuẩn bị các biến đếm số lượng học sinh
            $totalRollCalledStudents = 0; // Số học sinh đã điểm danh (có mặt)
            $totalStudentNotAttendance = 0; // Số học sinh vắng mặt không phép
            $totalStudentPolicy = 0; // Số học sinh đi muộn có phép
        
            // Chuẩn bị mảng dữ liệu trả về
            $data = $rollCallHistories->map(function ($history) use (&$totalRollCalledStudents, &$totalStudentNotAttendance, &$totalStudentPolicy) {
                $gender = $history->student ? GenderEnum::from($history->student->gender)->value : 'Unknown';
                
                // Kiểm tra trạng thái điểm danh và cập nhật các biến đếm
                switch ($history->status) {
                    case StatusStudentEnum::PRESENT->value:
                        $totalRollCalledStudents++; // Học sinh có mặt
                        break;
                    case StatusStudentEnum::UN_PRESENT->value:
                        $totalStudentNotAttendance++; // Học sinh vắng mặt không phép
                        break;
                    case StatusStudentEnum::LATE->value:
                    case StatusStudentEnum::UN_PRESENT_PER->value:
                        $totalStudentPolicy++; // Học sinh đến muộn có phép
                        break;
                }
        
                return [
                    'fullname' => $history->student ? $history->student->fullname : 'Unknown',
                    'student_code' => $history->student ? $history->student->student_code : 'Unknown',
                    'dob' => $history->student ? strtotime($history->student->dob) : null, // Ngày sinh
                    'gender' => $gender,
                    'note' => $history->note ?? 'Không có ghi chú', // Ghi chú nếu có
                    'status' => StatusStudentEnum::from($history->status)->value, // Trạng thái điểm danh (PRESENT, UN_PRESENT, etc.)
                ];
            });
        
            return [
                'message' => 'Lấy chi tiết lịch sử điểm danh thành công',
                'status' => 'success',
                'date' => Carbon::parse($date)->translatedFormat('l, d/m/Y'), // Hiển thị ngày đã chọn
                'class_id' => $classId,
                'total_students' => $totalStudents, // Tổng số học sinh
                'total_Students_Attended' => $totalRollCalledStudents, // Tổng số học sinh đã điểm danh
                'total_Student_NotAttendance' => $totalStudentNotAttendance, // Số học sinh vắng mặt không phép
                'total_Student_Policy' => $totalStudentPolicy, // Số học sinh đến muộn có phép
                'data' => $data, // Dữ liệu chi tiết của các học sinh đã điểm danh
            ];
        }

   
    
}