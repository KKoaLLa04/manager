<?php

namespace App\Domain\ParentRollCallHistory\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusStudentEnum;
use App\Domain\ParentRollCallHistory\Models\ParentRollCallHistory;
use App\Domain\RollCallHistory\Models\RollCallHistory;
use App\Models\Student;
use App\Models\StudentClassHistory;
use App\Models\UserStudent;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class ParentRollCallHistoryRepository
{
    public function getParentStudentRollCallHistories($userId, $pageSize, $keyWord = null, $date = null, $studentId = null)
    {
        // Lấy danh sách student_id của các con của phụ huynh
        $studentIds = UserStudent::where('user_id', $userId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->pluck('student_id');
    
        if ($studentIds->isEmpty()) {
            return [
                'message' => 'Không có học sinh nào',
                'status' => 'error',
                'data' => [],
                'total' => 0,
                'pageIndex' => 1,
                'pageSize' => $pageSize,
            ];
        }
    
        // Kiểm tra nếu truyền vào student_id không tồn tại trong danh sách studentIds
        if ($studentId && !$studentIds->contains($studentId)) {
            return [
                'message' => 'Học sinh không tồn tại hoặc không thuộc quyền quản lý của phụ huynh này',
                'status' => 'error',
                'data' => [],
                'total' => 0,
                'pageIndex' => 1,
                'pageSize' => $pageSize,
            ];
        }
    
        // Nếu không truyền student_id, lấy student_id đầu tiên
        $studentId = $studentId ?? $studentIds->first();
    
        // Tạo truy vấn lấy lịch sử điểm danh cho student_id
        $rollCallHistoriesQuery = ParentRollCallHistory::where('student_id', $studentId)
        ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
        ->with(['user' => function ($query) {
            $query->select('id', 'fullname', 'email');
        }, 'classes' => function ($query) {
            $query->select('id', 'name'); // Chọn các trường cần thiết của bảng classes
        }])  // Thêm quan hệ classes để lấy tên lớp
        ->orderBy('date', 'desc');
            
        // Lọc theo từ khóa nếu có
        if ($keyWord) {
            $rollCallHistoriesQuery->whereHas('user', function ($query) use ($keyWord) {
                $query->where('fullname', 'like', '%' . $keyWord . '%')
                    ->orWhere('email', 'like', '%' . $keyWord . '%');
            });
        }
    
        // Lọc theo ngày nếu có
        if ($date) {
            $rollCallHistoriesQuery->whereDate('date', $date);
        }
    
        // Lấy kết quả lịch sử điểm danh
        $rollCallHistories = $rollCallHistoriesQuery->get()->groupBy(function ($history) {
            return Carbon::parse($history->date)->toDateString();
        });
    
        // Nếu không có lịch sử điểm danh cho học sinh này
        if ($rollCallHistories->isEmpty()) {
            return [
                'message' => 'Con của phụ huynh chưa có lịch sử điểm danh.',
                'status' => 'error',
                'data' => [],
                'total' => 0,
                'pageIndex' => 1,
                'pageSize' => $pageSize,
            ];
        }
    
        // Xử lý dữ liệu lịch sử điểm danh
        $data = $rollCallHistories->map(function ($historiesPerDay) {
            $firstHistory = $historiesPerDay->first();
            $formattedDate = Carbon::parse($firstHistory->date)->translatedFormat('d/m/Y');
    
            return [
                'note' => $firstHistory->note,  
                'date' => $formattedDate,  
                'status' => $firstHistory->status,
            ];
        })->values();
    
        // Tên học sinh và lớp học (tách ra ngoài data)
        $studentName = $rollCallHistories->first()->first()->student->fullname ?? 'Chưa có học sinh';
        $className = $rollCallHistories->first()->first()->classes->name ?? 'Chưa có lớp';
    
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
    
        // Trả về kết quả với student_name và class_name ra ngoài data
        return [
            'message' => 'Lấy lịch sử điểm danh thành công',
            'status' => 'success',
            'student_name' => $studentName,  // Tên học sinh ở ngoài data
            'class_name' => $className,  // Tên lớp ở ngoài data
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'pageIndex' => $paginator->currentPage(),
            'pageSize' => $paginator->perPage(),
        ];
    }
    

    
    // public function getParentStudentRollCallHistories($userId, $pageSize, $keyWord = null, $date = null, $studentId = null)
    // {
    //     // Lấy danh sách student_id của các con của phụ huynh
    //     $studentIds = UserStudent::where('user_id', $userId)
    //         ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
    //         ->pluck('student_id');
    
    //     if ($studentIds->isEmpty()) {
    //         return [
    //             'message' => 'Không có học sinh nào',
    //             'status' => 'error',
    //             'data' => [],
    //             'total' => 0,
    //             'pageIndex' => 1,
    //             'pageSize' => $pageSize,
    //         ];
    //     }
    
    //     // Kiểm tra nếu truyền vào student_id không tồn tại trong danh sách studentIds
    //     if ($studentId && !$studentIds->contains($studentId)) {
    //         return [
    //             'message' => 'Học sinh không tồn tại hoặc không thuộc quyền quản lý của phụ huynh này',
    //             'status' => 'error',
    //             'data' => [],
    //             'total' => 0,
    //             'pageIndex' => 1,
    //             'pageSize' => $pageSize,
    //         ];
    //     }
    
    //     // Nếu không truyền student_id, lấy student_id đầu tiên
    //     $studentId = $studentId ?? $studentIds->first();
    
    //     // Tạo truy vấn lấy lịch sử điểm danh cho student_id
    //     $rollCallHistoriesQuery = ParentRollCallHistory::where('student_id', $studentId)
    //         ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
    //         ->with(['user' => function ($query) {
    //             $query->select('id', 'fullname', 'email');
    //         }, 'classes' => function ($query) {
    //             $query->select('id', 'name'); // Chọn các trường cần thiết của bảng classes
    //         }])  // Thêm quan hệ classes để lấy tên lớp
    //         ->orderBy('date', 'desc');
    
    //     // Lọc theo từ khóa nếu có
    //     if ($keyWord) {
    //         $rollCallHistoriesQuery->whereHas('user', function ($query) use ($keyWord) {
    //             $query->where('fullname', 'like', '%' . $keyWord . '%')
    //                 ->orWhere('email', 'like', '%' . $keyWord . '%');
    //         });
    //     }
    
    //     // Lọc theo ngày nếu có
    //     if ($date) {
    //         $rollCallHistoriesQuery->whereDate('date', $date);
    //     }
    
    //     // Lấy danh sách điểm danh
    //     $rollCallHistories = $rollCallHistoriesQuery->get()->groupBy(function ($history) {
    //         return Carbon::parse($history->date)->toDateString();
    //     });
    
    //     // Các biến đếm số lượng học sinh có mặt, vắng mặt và đi muộn có phép
    //     $totalRollCalledStudents = 0; // Số học sinh đã điểm danh (có mặt)
    //     $totalStudentNotAttendance = 0; // Số học sinh vắng mặt không phép
    //     $totalStudentPolicy = 0; // Số học sinh đi muộn có phép
    
    //     // Xử lý dữ liệu để chỉ lấy note, date, status và cập nhật số lượng học sinh theo trạng thái
    //     $data = $rollCallHistories->map(function ($historiesPerDay) use (&$totalRollCalledStudents, &$totalStudentNotAttendance, &$totalStudentPolicy) {
    //         $firstHistory = $historiesPerDay->first();
    //         $formattedDate = Carbon::parse($firstHistory->date)->translatedFormat('d/m/Y');
    
    //         // Kiểm tra trạng thái điểm danh và cập nhật các biến đếm
    //         $status = $firstHistory->status;
    //         switch ($status) {
    //             case StatusStudentEnum::PRESENT->value:
    //                 $totalRollCalledStudents++; // Học sinh có mặt
    //                 break;
    //             case StatusStudentEnum::UN_PRESENT->value:
    //                 $totalStudentNotAttendance++; // Học sinh vắng mặt không phép
    //                 break;
    //             case StatusStudentEnum::LATE->value:
    //             case StatusStudentEnum::UN_PRESENT_PER->value:
    //                 $totalStudentPolicy++; // Học sinh đến muộn có phép
    //                 break;
    //         }
    
    //         return [
    //             'date' => $formattedDate,  
    //             'note' => $firstHistory->note,  
    //             'status' => $firstHistory->status,
    //         ];
    //     })->values();
    
    //     // Tên học sinh và lớp học (tách ra ngoài data)
    //     $studentName = $rollCallHistories->first()->first()->student->fullname ?? 'Chưa có học sinh';
    //     $className = $rollCallHistories->first()->first()->classes->name ?? 'Chưa có lớp';
    
    //     // Phân trang dữ liệu
    //     $totalDays = $data->count();
    //     $currentPage = LengthAwarePaginator::resolveCurrentPage();
    //     $paginatedData = $data->slice(($currentPage - 1) * $pageSize, $pageSize)->values();
    
    //     $paginator = new LengthAwarePaginator(
    //         $paginatedData,
    //         $totalDays,
    //         $pageSize,
    //         $currentPage,
    //         ['path' => LengthAwarePaginator::resolveCurrentPath()]
    //     );
    
    //     // Trả về kết quả với student_name và class_name ra ngoài data
    //     return [
    //         'message' => 'Lấy lịch sử điểm danh thành công',
    //         'status' => 'success',
    //         'student_name' => $studentName,  // Tên học sinh ở ngoài data
    //         'class_name' => $className,  // Tên lớp ở ngoài data
    //         'total_roll_called_students' => $totalRollCalledStudents,  // Tổng số học sinh có mặt
    //         'total_student_not_attendance' => $totalStudentNotAttendance,  // Tổng số học sinh vắng mặt không phép
    //         'total_student_policy' => $totalStudentPolicy,  // Tổng số học sinh đi muộn có phép
    //         'data' => $paginator->items(),
    //         'total' => $paginator->total(),
    //         'pageIndex' => $paginator->currentPage(),
    //         'pageSize' => $paginator->perPage(),
    //     ];
    // }
    
    
    


    
    
    
    
    
}
