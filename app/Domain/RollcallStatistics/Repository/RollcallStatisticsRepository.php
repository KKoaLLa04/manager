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
use Carbon\CarbonPeriod;
use Illuminate\Pagination\LengthAwarePaginator;

class RollcallStatisticsRepository {


    public function listClasses($pageSize, $keyWord = null, $status = null, $classId = null)
    {    
        $query = Classes::where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->with(['grade', 'classHistory' => function ($query) {
                $query->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                    ->where('status', StatusEnum::ACTIVE->value)
                    ->where(function ($q) {
                        $q->whereNull('end_date') // Nếu end_date là null
                            ->orWhere('end_date', '>=', now()); // Nếu end_date là ngày sau ngày hiện tại
                    });
            }, 'classSubjectTeacher.user' => function ($query) {
                $query->select('id', 'fullname', 'email');
            }]);

        // Nếu có từ khóa tìm kiếm
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

        // Nếu có status, lọc theo status
        if ($status) {
            $query->whereHas('classHistory', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        // Nếu có classId, lọc theo classId
        if ($classId) {
            $query->where('id', $classId);
        }


        $classes = $query->paginate($pageSize);

        return [
            'total' => $classes->total(),
            'data' => $classes->map(function ($class) {
                $mainTeacher = $class->classSubjectTeacher
                    ->where('access_type', StatusTeacherEnum::MAIN_TEACHER->value)
                    ->first()->user ?? null;

                return [
                    'class_name' => $class->name ?? null,
                    'grade_name' => $class->grade->name ?? null,
                    'status_class' => $class->status ?? null,
                    'teacher_name' => $mainTeacher ? $mainTeacher->fullname : 'Chưa có giáo viên chủ nhiệm',
                    'total_students' => $class->classHistory->count(),
                ];
            }),
            'current_page' => $classes->currentPage(),
            'per_page' => $classes->perPage(),
        ];
    }

    
    

    public function getClassRollCall($classId, $pageSize, $date = null)
    {
        // Lấy tên lớp
        $className = Classes::where('id', $classId)->value('name'); 
    
        // Lấy danh sách học sinh chưa nghỉ học và thuộc lớp
        $students = StudentClassHistory::where('class_id', $classId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where(function ($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>', now());
            })
            ->with(['student' => function ($query) {
                $query->select('id', 'fullname', 'student_code');
            }])
            ->get()
            ->unique('student_id');
    
        // Xử lý khoảng thời gian
        $startDate = $date 
            ? Carbon::parse($date)->format('Y-m-d') 
            : now()->startOfMonth()->format('Y-m-d');
    
        $endDate = $date 
            ? Carbon::parse($date)->endOfMonth()->format('Y-m-d') 
            : now()->format('Y-m-d');
    
        // Danh sách ngày từ startDate -> endDate
        $dateRange = collect();
        for ($currentDate = Carbon::parse($startDate); $currentDate->lte(Carbon::parse($endDate)); $currentDate->addDay()) {
            $dateRange->push($currentDate->format('Y-m-d'));
        }
    
        // Format dữ liệu
        $data = $students->map(function ($studentClassHistory) use ($dateRange) {
            $student = $studentClassHistory->student;
    
            // Lấy tổng số ngày nghỉ, đi muộn, có mặt từ bảng roll_call_history
            $attendanceStats = RollCallHistory::where('student_id', $student->id)
                ->where('class_id', $studentClassHistory->class_id)
                ->whereBetween('date', [$dateRange->first(), $dateRange->last()])
                ->selectRaw('
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as total_present,
                    SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END) as total_absent,
                    SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as total_late
                ', [
                    StatusStudentEnum::PRESENT->value,
                    StatusStudentEnum::UN_PRESENT->value,
                    StatusStudentEnum::UN_PRESENT_PER->value,
                    StatusStudentEnum::LATE->value,
                ])
                ->first();
    
            // Lấy dữ liệu điểm danh từ bảng RollCallHistory
            $attendanceDetails = RollCallHistory::where('student_id', $student->id)
                ->where('class_id', $studentClassHistory->class_id)
                ->whereBetween('date', [$dateRange->first(), $dateRange->last()])
                ->get(['date', 'status'])
                ->mapWithKeys(function ($attendance) {
                    return [$attendance->date => $attendance->status];
                });
    
            // Tạo danh sách chi tiết từng ngày (thêm ngày không điểm danh với status = 5)
            $detailedAttendance = $dateRange->map(function ($date) use ($attendanceDetails) {
                return [
                    'date' => \Carbon\Carbon::parse($date)->format('d/m/Y'),
                    'status' => $attendanceDetails->get($date, StatusStudentEnum::HOLIDAY->value,), // Mặc định status = 5
                ];
            });
    
            return [
                'student_name' => $student->fullname,
                'student_code' => $student->student_code,
                'total_present' => $attendanceStats->total_present ?? 0,
                'total_absent' => $attendanceStats->total_absent ?? 0,
                'total_late' => $attendanceStats->total_late ?? 0,
                'date' => $detailedAttendance,
            ];
        })->values();
    
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
            'class_id'=>$classId,
            'class_name' => $className,
            'total_students' => $totalStudents,
            'data' => $paginator->items(),
            'total' => $paginator->total(),
         
        ];
    }
    
    
    
    
    


    
    
    
    
    
    

    
}