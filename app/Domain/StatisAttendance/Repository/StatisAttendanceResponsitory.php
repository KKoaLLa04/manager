<?php

namespace App\Domain\StatisAttendance\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusStudentEnum;
use App\Domain\StatisAttendance\Models\StatisAttendance;
use App\Models\Classes;
use Carbon\Carbon;

class StatisAttendanceResponsitory
{


    public function getStatisAttendanceSchoolOnDay()
    {
        $today = now();
        // Tính toán số lượng theo từng trạng thái
        $allAtten = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereDate('date', $today)
            ->where('status', StatusStudentEnum::PRESENT->value)
            ->count();

        $allUnattend = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereDate('date', $today)
            ->where('status', StatusStudentEnum::UN_PRESENT->value)
            ->count();

        $allunpresentper = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereDate('date', $today)
            ->where('status', StatusStudentEnum::UN_PRESENT_PER->value)
            ->count();

        $allLate = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereDate('date', $today)
            ->where('status', StatusStudentEnum::LATE->value)
            ->count();

        return [
            'allAttendances' => $allAtten,
            'allUnattend' => $allUnattend,
            'allunpresentper' => $allunpresentper,
            'allLate' => $allLate,
        ];
    }


    public function getStatisAttendanceSchoolOnWeek()
    {
        $startOfWeek = Carbon::now()->startOfWeek(); // Lấy ngày đầu tuần (thứ 2)
        $endOfWeek = Carbon::now()->endOfWeek();     // Lấy ngày cuối tuần (chủ nhật)

        // Tính toán số lượng theo từng trạng thái trong tuần
        $allAtten = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->where('status', StatusStudentEnum::PRESENT->value)
            ->count();

        $allUnattend = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->where('status', StatusStudentEnum::UN_PRESENT->value)
            ->count();

        $allunpresentper = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->where('status', StatusStudentEnum::UN_PRESENT_PER->value)
            ->count();

        $allLate = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereBetween('date', [$startOfWeek, $endOfWeek])
            ->where('status', StatusStudentEnum::LATE->value)
            ->count();

        return [
            'allAttendances' => $allAtten,
            'allUnattend' => $allUnattend,
            'allunpresentper' => $allunpresentper,
            'allLate' => $allLate,
        ];
    }

    public function getStatisAttendanceSchoolOnMonth()
    {
        $startOfMonth = Carbon::now()->startOfMonth(); // Lấy ngày đầu tháng
        $endOfMonth = Carbon::now()->endOfMonth();     // Lấy ngày cuối tháng

        // Tính toán số lượng theo từng trạng thái trong tuần
        $allAtten = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('status', StatusStudentEnum::PRESENT->value)
            ->count();

        $allUnattend = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('status', StatusStudentEnum::UN_PRESENT->value)
            ->count();

        $allunpresentper = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('status', StatusStudentEnum::UN_PRESENT_PER->value)
            ->count();

        $allLate = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->where('status', StatusStudentEnum::LATE->value)
            ->count();

        return [
            'allAttendances' => $allAtten,
            'allUnattend' => $allUnattend,
            'allunpresentper' => $allunpresentper,
            'allLate' => $allLate,
        ];
    }

    public function getStatisAttendanceSchoolOnDayWithClass()
    {
        $today = Carbon::today();  // Lấy ngày hôm nay
        $classes = Classes::where('is_deleted', DeleteEnum::NOT_DELETE->value)->get(); // Lấy tất cả các lớp không bị xóa

        $statistics = $classes->map(function ($class) use ($today) {
            // Lấy điểm danh mới nhất của từng học sinh trong lớp trong ngày hôm nay
            $studentsAttendance = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereDate('date', $today)  // So sánh ngày
                ->where('class_id', $class->id)
                ->where('status', StatusStudentEnum::PRESENT->value)  // Trạng thái 'Có mặt'
                ->get()
                ->groupBy('student_id')  // Nhóm theo student_id
                ->map(function ($attendanceRecords) {
                    // Lấy bản ghi mới nhất của mỗi học sinh
                    return $attendanceRecords->sortByDesc('time')->first(); // Sắp xếp theo ngày và lấy bản ghi mới nhất
                });

            $allUnattend = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereDate('date', $today)  // So sánh ngày
                ->where('class_id', $class->id)
                ->where('status', StatusStudentEnum::UN_PRESENT->value)  // Trạng thái 'Có mặt'
                ->get()
                ->groupBy('student_id')  // Nhóm theo student_id
                ->map(function ($attendanceRecords) {
                    // Lấy bản ghi mới nhất của mỗi học sinh
                    return $attendanceRecords->sortByDesc('time')->first(); // Sắp xếp theo ngày và lấy bản ghi mới nhất
                });

            $allunpresentper = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereDate('date', $today)  // So sánh ngày
                ->where('class_id', $class->id)
                ->where('status', StatusStudentEnum::UN_PRESENT_PER->value)  // Trạng thái 'Có mặt'
                ->get()
                ->groupBy('student_id')  // Nhóm theo student_id
                ->map(function ($attendanceRecords) {
                    // Lấy bản ghi mới nhất của mỗi học sinh
                    return $attendanceRecords->sortByDesc('time')->first(); // Sắp xếp theo ngày và lấy bản ghi mới nhất
                });

            $allLate = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereDate('date', $today)  // So sánh ngày
                ->where('class_id', $class->id)
                ->where('status', StatusStudentEnum::LATE->value)  // Trạng thái 'Có mặt'
                ->get()
                ->groupBy('student_id')  // Nhóm theo student_id
                ->map(function ($attendanceRecords) {
                    // Lấy bản ghi mới nhất của mỗi học sinh
                    return $attendanceRecords->sortByDesc('time')->first(); // Sắp xếp theo ngày và lấy bản ghi mới nhất
                });

            // Đếm số lượng học sinh có mặt trong lớp (sau khi lấy bản ghi mới nhất cho từng học sinh)
            $allAtten = $studentsAttendance->count();
            $allUnattend = $allUnattend->count();
            $allunpresentper = $allunpresentper->count();
            $allLate = $allLate->count();

            return [
                'className' => $class->name,
                'allAtten' => $allAtten,
                'allUnattend' => $allUnattend,
                'allunpresentper' => $allunpresentper,
                'allLate' => $allLate
            ];
        });

        return $statistics;
    }

    public function getStatisAttendanceSchoolOnWeekWithClass()
    {
        $startOfWeek = Carbon::now()->startOfWeek(); // Lấy ngày đầu tuần (thứ 2)
        $endOfWeek = Carbon::now()->endOfWeek();     // Lấy ngày cuối tuần (chủ nhật)
        $classes = Classes::where('is_deleted', DeleteEnum::NOT_DELETE->value)->get(); // Lấy tất cả các lớp không bị xóa

        // Hàm chung để lấy bản ghi mới nhất cho từng trạng thái
        $getLatestAttendance = function ($classId, $startOfWeek, $endOfWeek, $status) {
            return StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereBetween('date', [$startOfWeek, $endOfWeek])
                ->where('class_id', $classId)
                ->where('status', $status)
                ->get()
                ->groupBy('student_id')
                ->map(function ($attendanceRecords) {
                    // Lấy bản ghi mới nhất của mỗi học sinh
                    return $attendanceRecords->sortByDesc('time')->first(); // Sắp xếp theo thời gian và lấy bản ghi mới nhất
                });
        };

        $statistics = $classes->map(function ($class) use ($startOfWeek, $endOfWeek, $getLatestAttendance) {
            // Lấy điểm danh mới nhất cho từng học sinh trong lớp trong tuần này
            $studentsAttendance = $getLatestAttendance($class->id, $startOfWeek, $endOfWeek, StatusStudentEnum::PRESENT->value);
            $allUnattend = $getLatestAttendance($class->id, $startOfWeek, $endOfWeek, StatusStudentEnum::UN_PRESENT->value);
            $allUnpresentPer = $getLatestAttendance($class->id, $startOfWeek, $endOfWeek, StatusStudentEnum::UN_PRESENT_PER->value);
            $allLate = $getLatestAttendance($class->id, $startOfWeek, $endOfWeek, StatusStudentEnum::LATE->value);

            // Đếm số lượng học sinh có mặt, vắng, vắng không phép, và muộn trong lớp
            $allAtten = $studentsAttendance->count();
            $allUnattend = $allUnattend->count();
            $allUnpresentPer = $allUnpresentPer->count();
            $allLate = $allLate->count();

            return [
                'className' => $class->name,
                'allAtten' => $allAtten,
                'allUnattend' => $allUnattend,
                'allunpresentper' => $allUnpresentPer,
                'allLate' => $allLate
            ];
        });

        return $statistics; // Trả về kết quả cho tất cả các lớp
    }

    public function getStatisAttendanceSchoolOnMonthWithClass()
    {
        $startOfMonth = Carbon::now()->startOfMonth(); // Lấy ngày đầu tháng
        $endOfMonth = Carbon::now()->endOfMonth();     // Lấy ngày cuối tháng
        $classes = Classes::where('is_deleted', DeleteEnum::NOT_DELETE->value)->get(); // Lấy tất cả các lớp không bị xóa

        // Hàm chung để lấy bản ghi mới nhất cho từng trạng thái
        $getLatestAttendance = function ($classId, $startOfMonth, $endOfMonth, $status) {
            return StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->where('class_id', $classId)
                ->where('status', $status)
                ->get()
                ->groupBy('student_id')
                ->map(function ($attendanceRecords) {
                    // Lấy bản ghi mới nhất của mỗi học sinh
                    return $attendanceRecords->sortByDesc('time')->first(); // Sắp xếp theo thời gian và lấy bản ghi mới nhất
                });
        };

        $statistics = $classes->map(function ($class) use ($startOfMonth, $endOfMonth, $getLatestAttendance) {
            // Lấy điểm danh mới nhất cho từng học sinh trong lớp trong tuần này
            $studentsAttendance = $getLatestAttendance($class->id, $startOfMonth, $endOfMonth, StatusStudentEnum::PRESENT->value);
            $allUnattend = $getLatestAttendance($class->id, $startOfMonth, $endOfMonth, StatusStudentEnum::UN_PRESENT->value);
            $allUnpresentPer = $getLatestAttendance($class->id, $startOfMonth, $endOfMonth, StatusStudentEnum::UN_PRESENT_PER->value);
            $allLate = $getLatestAttendance($class->id, $startOfMonth, $endOfMonth, StatusStudentEnum::LATE->value);

            // Đếm số lượng học sinh có mặt, vắng, vắng không phép, và muộn trong lớp
            $allAtten = $studentsAttendance->count();
            $allUnattend = $allUnattend->count();
            $allUnpresentPer = $allUnpresentPer->count();
            $allLate = $allLate->count();

            return [
                'className' => $class->name,
                'allAtten' => $allAtten,
                'allUnattend' => $allUnattend,
                'allunpresentper' => $allUnpresentPer,
                'allLate' => $allLate
            ];
        });

        return $statistics; // Trả về kết quả cho tất cả các lớp
    }
}
