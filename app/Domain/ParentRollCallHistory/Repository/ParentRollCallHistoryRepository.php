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
    
        // Biến đếm tổng số lần có mặt và vắng mặt
        $totalPresent = 0;
        $totalAbsent = 0;
        $totalLate = 0;
    
        // Xử lý dữ liệu lịch sử điểm danh
        $data = $rollCallHistories->map(function ($historiesPerDay) use (&$totalPresent, &$totalAbsent, &$totalLate) {
            $firstHistory = $historiesPerDay->first();
            $timestamp = Carbon::parse($firstHistory->date)->timestamp;
    
            if ($firstHistory->status === StatusStudentEnum::PRESENT->value) {
                $totalPresent++;
            } elseif ($firstHistory->status === StatusStudentEnum::UN_PRESENT->value) {
                $totalAbsent++;
            } elseif ($firstHistory->status === StatusStudentEnum::LATE->value){
                $totalLate++;
            } 
    
            return [
                'note' => $firstHistory->note,  
                'date' => $timestamp,  
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
            'student_name' => $studentName,  
            'class_name' => $className,  
            'total_present' => $totalPresent,  
            'total_absent' => $totalAbsent,  
            'total_late' => $totalLate,  
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'pageIndex' => $paginator->currentPage(),
            'pageSize' => $paginator->perPage(),
        ];
    }
    
    


    
    
    


    
    
    
    
    
}
