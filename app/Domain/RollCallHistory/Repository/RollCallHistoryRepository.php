<?php
namespace App\Domain\RollCallHistory\Repository;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\GenderEnum;
use App\Common\Enums\PaginateEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusStudentEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\RollCallHistory\Models\RollCallHistory;
use App\Models\Classes;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\StudentClassHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RollCallHistoryRepository {


    public function getClassesWithRollCallHistories($pageSize, $keyWord = null)
    {    
        $query = Classes::where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->with(['grade', 'classHistory' => function ($query) {
                $query->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                      ->where('status', StatusEnum::ACTIVE->value)
                      ->whereNull('end_date'); 
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
    
    
    
    

    // public function getClassRollCallHistories($classId, $pageSize, $keyWord = null, $Date = null)
    // {
    //     // Tính tổng số học sinh
    //     $totalStudents = StudentClassHistory::where('class_id', $classId)
    //         ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
    //         ->count();
    
    //     // Lấy danh sách lịch sử điểm danh với điều kiện tìm kiếm (nếu có)
    //     $rollCallHistoriesQuery = RollCallHistory::where('class_id', $classId)
    //     ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
    //     ->with(['user' => function ($query) {
    //         $query->select('id', 'fullname', 'email');
    //     }])
    //     ->orderBy('date', 'desc'); 

    //     if ($keyWord) {
    //         $rollCallHistoriesQuery->whereHas('user', function ($query) use ($keyWord) {
    //             $query->where('fullname', 'like', '%' . $keyWord . '%')
    //                 ->orWhere('email', 'like', '%' . $keyWord . '%');
    //         });
    //     }
    //       // Nếu có ngày cụ thể, thêm điều kiện lọc theo ngày
    //     if ($Date) {
    //         $rollCallHistoriesQuery->whereDate('date', '=', Carbon::parse($Date)->toDateString());
    //     }
    //     $rollCallHistories = $rollCallHistoriesQuery->paginate($pageSize);

    //     // Lấy dữ liệu từ query
    //     $rollCallHistories = $rollCallHistoriesQuery->get()->groupBy(function($history) {
    //         return Carbon::parse($history->date)->toDateString(); // Nhóm theo ngày (YYYY-MM-DD)
    //     });

    
    //     // Tạo mảng kết quả chỉ lấy bản ghi đầu tiên của mỗi ngày
    //     $data = $rollCallHistories->map(function ($historiesPerDay) {
    //         $firstHistory = $historiesPerDay->first(); // Lấy bản ghi đầu tiên trong ngày
    //         $formattedDate = Carbon::parse($firstHistory->date)->translatedFormat('l, d/m/Y'); // Format ngày
    //         $totalRollCalledStudents = $historiesPerDay->count(); // Tính tổng số học sinh đã điểm danh trong ngày

    //         return [
    //             'class_id' => $firstHistory->class_id,
    //             'date' => $formattedDate,
    //             'roll_call_by' => $firstHistory->user ? $firstHistory->user->fullname : 'Unknown',
    //             'roll_call_email' => $firstHistory->user ? $firstHistory->user->email : 'Unknown',
    //             'total_Studente_Attendenced' => $totalRollCalledStudents,
    //         ];
    //     });
    
    //     // Tính tổng số bản ghi (chỉ tính số ngày không trùng nhau)
    //     $totalDays = $data->count();
    
    //     return [
    //         'message' => 'Lấy lịch sử điểm danh thành công',
    //         'status' => 'success',
    //         'total_students' => $totalStudents, // Tổng số học sinh
    //         'data' => $data->values(), // Kết quả không trùng lặp
    //         'total' => $totalDays, // Tổng số ngày
    //         'pageIndex' => 1, // Mặc định 1 vì không phân trang theo ngày
    //         'pageSize' => $pageSize,
    //     ];
    // }
    
    

    public function getClassRollCallHistories($classId, $pageSize, $keyWord = null, $date = null)
    {
        // Tính tổng số học sinh
        $totalStudents = StudentClassHistory::where('class_id', $classId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereNull('end_date')
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
    
        $rollCallHistories = $rollCallHistoriesQuery->get()->groupBy(function($history) {
            return Carbon::parse($history->date)->toDateString(); // Nhóm theo ngày (YYYY-MM-DD)
        });
    
        // Tạo mảng kết quả chỉ lấy bản ghi đầu tiên của mỗi ngày
        $data = $rollCallHistories->map(function ($historiesPerDay) {
            $firstHistory = $historiesPerDay->first(); // Lấy bản ghi đầu tiên trong ngày
            $formattedDate = Carbon::parse($firstHistory->date)->translatedFormat('l, d/m/Y'); // Format ngày
            $totalPresentStudents = $historiesPerDay->where('status', StatusStudentEnum::PRESENT->value)->count();    
            return [
                'class_id' => $firstHistory->class_id,
                'date' => $formattedDate,
                'teacherHomeRoomName' => $firstHistory->user ? $firstHistory->user->fullname : 'Unknown',
                'teacherHomeRoomGmail' => $firstHistory->user ? $firstHistory->user->email : 'Unknown',
                'total_Studente_Attendenced' => $totalPresentStudents,
            ];
        })->values(); // Đảm bảo trả về danh sách không có key
    
        // Phân trang thủ công sau khi nhóm
        $totalDays = $data->count(); // Tổng số ngày không trùng nhau
        $currentPage = LengthAwarePaginator::resolveCurrentPage(); // Lấy trang hiện tại từ request
        $paginatedData = $data->slice(($currentPage - 1) * $pageSize, $pageSize)->values(); // Phân trang
    
        // Tạo đối tượng phân trang
        $paginator = new LengthAwarePaginator(
            $paginatedData, // Dữ liệu đã phân trang
            $totalDays, // Tổng số bản ghi
            $pageSize, // Số bản ghi trên mỗi trang
            $currentPage, // Trang hiện tại
            ['path' => LengthAwarePaginator::resolveCurrentPath()] // Đường dẫn hiện tại cho phân trang
        );
    
        // Trả về kết quả
        return [
            'message' => 'Lấy lịch sử điểm danh thành công',
            'status' => 'success',
            'total_students' => $totalStudents, // Tổng số học sinh
            'data' => $paginator->items(), // Dữ liệu đã phân trang
            'total' => $paginator->total(), // Tổng số ngày
            'pageIndex' => $paginator->currentPage(), // Trang hiện tại
            'pageSize' => $paginator->perPage(), // Số bản ghi mỗi trang
        ];
    }

    
    public function getClassRollCallHistoryDetailsByDate($classId, $date) 
    {
        // Tính tổng số học sinh tại lớp tính đến ngày được chỉ định, bỏ qua các học sinh đã chuyển lớp
        $totalStudents = StudentClassHistory::where('class_id', $classId)
        ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
        ->where('end_date',)
        ->where(function ($query) use ($date) {
            $query->whereNull('end_date')            // Học sinh chưa rời lớp
                    ->orWhereDate('end_date', '>', $date);  // Hoặc còn học trong lớp sau ngày `$date`
        })
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