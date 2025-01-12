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
        $today = now(); // Lấy ngày hiện tại
        $classes = Classes::where('is_deleted', DeleteEnum::NOT_DELETE->value)->get(); // Lấy tất cả các lớp

        // Khởi tạo các biến đếm
        $totalAttendances = 0;
        $allUnattend = 0;
        $allunpresentper = 0;
        $allLate = 0;

        // Lặp qua tất cả các lớp
        foreach ($classes as $class) {
            // Lấy danh sách các bản ghi điểm danh mới nhất cho từng học sinh trong tiết học cuối cùng
            $lastTimetable = StatisAttendance::select('teacher_subject_timetable_id', 'created_at')
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereDate('date', $today)
            ->where('class_id', $class->id)
            ->distinct() // Lọc ra các tiết học
            ->orderBy('created_at', 'desc') // Sắp xếp theo `created_at` giảm dần
            ->first();

            if ($lastTimetable) {
                // Lấy các bản ghi điểm danh của tiết học cuối cùng
                $allAtten = StatisAttendance::select('student_id', 'status', 'teacher_subject_timetable_id')
                    ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                    ->whereDate('date', $today)
                    ->where('teacher_subject_timetable_id', $lastTimetable->teacher_subject_timetable_id) // Lọc theo tiết học cuối cùng
                    ->where('status', StatusStudentEnum::PRESENT->value)
                    ->orderBy('student_id') // Đảm bảo nhóm theo student_id
                    ->orderBy('created_at', 'desc') // Lấy bản ghi mới nhất
                    ->get()
                    ->unique('student_id'); // Loại bỏ bản ghi trùng `student_id`

                // Đếm tổng số học sinh có mặt trong tiết học cuối cùng
                $totalAttendances += $allAtten->count();
            }

            // Đếm số học sinh không tham gia trong tiết học cuối cùng
            $allUnattend += StatisAttendance::select('student_id', 'teacher_subject_timetable_id')
                ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereDate('date', $today)
                ->where('status', StatusStudentEnum::UN_PRESENT->value)
                ->where('teacher_subject_timetable_id', $lastTimetable ? $lastTimetable->teacher_subject_timetable_id : null) // Lọc theo tiết học cuối cùng
                ->count();

            // Đếm số học sinh không tham gia tạm thời trong tiết học cuối cùng
            $allunpresentper += StatisAttendance::select('student_id', 'teacher_subject_timetable_id')
                ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereDate('date', $today)
                ->where('status', StatusStudentEnum::UN_PRESENT_PER->value)
                ->where('teacher_subject_timetable_id', $lastTimetable ? $lastTimetable->teacher_subject_timetable_id : null) // Lọc theo tiết học cuối cùng
                ->count();

            // Đếm số học sinh đi muộn trong tiết học cuối cùng
            $allLate += StatisAttendance::select('student_id', 'teacher_subject_timetable_id')
                ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereDate('date', $today)
                ->where('status', StatusStudentEnum::LATE->value)
                ->where('teacher_subject_timetable_id', $lastTimetable ? $lastTimetable->teacher_subject_timetable_id : null) // Lọc theo tiết học cuối cùng
                ->count();
        }

        return [
            'allAttendances' => $totalAttendances, // Tổng số học sinh có mặt trong tiết học cuối cùng
            'allUnattend' => $allUnattend, // Số học sinh vắng mặt không phép
            'allunpresentper' => $allunpresentper, // Số học sinh vắng mặt có phép
            'allLate' => $allLate, // Số học sinh đi muộn
        ];
    }




    public function getStatisAttendanceSchoolOnWeek()
    {
        $startOfWeek = Carbon::now()->startOfWeek(); // Lấy ngày đầu tuần (thứ 2)
        $endOfWeek = Carbon::now()->endOfWeek();     // Lấy ngày cuối tuần (chủ nhật)
        $classes = Classes::where('is_deleted', DeleteEnum::NOT_DELETE->value)->get(); // Lấy tất cả các lớp

        // Khởi tạo các biến đếm
        $totalAttendances = 0;
        $allUnattend = 0;
        $allunpresentper = 0;
        $allLate = 0;

        // Lặp qua tất cả các lớp
        foreach ($classes as $class) {
            // Lấy bản ghi tiết học cuối cùng trong tuần cho lớp hiện tại
            $lastTimetable = StatisAttendance::select('teacher_subject_timetable_id')
                ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereBetween('date', [$startOfWeek, $endOfWeek])
                ->where('class_id', $class->id) // Lọc theo lớp
                ->latest('created_at') // Lấy bản ghi mới nhất
                ->first();

            if ($lastTimetable) {
                // Lấy các bản ghi điểm danh cho tiết học cuối cùng và trạng thái PRESENT
                $totalAttendances += StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                    ->whereBetween('date', [$startOfWeek, $endOfWeek])
                    ->where('teacher_subject_timetable_id', $lastTimetable->teacher_subject_timetable_id)
                    ->where('status', StatusStudentEnum::PRESENT->value)
                    ->distinct('student_id') // Đảm bảo không tính trùng lặp học sinh
                    ->count();

                // Đếm số học sinh vắng mặt không phép
                $allUnattend += StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                    ->whereBetween('date', [$startOfWeek, $endOfWeek])
                    ->where('status', StatusStudentEnum::UN_PRESENT->value)
                    ->where('teacher_subject_timetable_id', $lastTimetable->teacher_subject_timetable_id)
                    ->count();

                // Đếm số học sinh vắng mặt có phép
                $allunpresentper += StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                    ->whereBetween('date', [$startOfWeek, $endOfWeek])
                    ->where('status', StatusStudentEnum::UN_PRESENT_PER->value)
                    ->where('teacher_subject_timetable_id', $lastTimetable->teacher_subject_timetable_id)
                    ->count();

                // Đếm số học sinh đi muộn
                $allLate += StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                    ->whereBetween('date', [$startOfWeek, $endOfWeek])
                    ->where('status', StatusStudentEnum::LATE->value)
                    ->where('teacher_subject_timetable_id', $lastTimetable->teacher_subject_timetable_id)
                    ->count();
            }
        }

        return [
            'allAttendances' => $totalAttendances, // Tổng số học sinh có mặt
            'allUnattend' => $allUnattend, // Số học sinh vắng mặt không phép
            'allunpresentper' => $allunpresentper, // Số học sinh vắng mặt có phép
            'allLate' => $allLate, // Số học sinh đi muộn
        ];
    }


    public function getStatisAttendanceSchoolOnMonth()
    {
        $startOfMonth = Carbon::now()->startOfMonth(); // Lấy ngày đầu tháng
        $endOfMonth = Carbon::now()->endOfMonth();     // Lấy ngày cuối tháng

        $classes = Classes::where('is_deleted', DeleteEnum::NOT_DELETE->value)->get(); // Lấy tất cả các lớp


        // Khởi tạo các biến đếm
        $totalAttendances = 0;
        $allUnattend = 0;
        $allunpresentper = 0;
        $allLate = 0;

        // Lặp qua tất cả các lớp
        foreach ($classes as $class) {
            // Lấy danh sách các bản ghi điểm danh mới nhất cho từng học sinh trong tiết học cuối cùng
         
                $lastTimetable = StatisAttendance::select('teacher_subject_timetable_id', 'created_at')
    ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
    ->whereBetween('date', [$startOfMonth, $endOfMonth])
    ->where('class_id', $class->id)
    ->distinct() // Lọc ra các tiết học
    ->orderBy('created_at', 'desc') // Sắp xếp theo `created_at` giảm dần
    ->first();

            if ($lastTimetable) {
                // Lấy các bản ghi điểm danh của tiết học cuối cùng
                $allAtten = StatisAttendance::select('student_id', 'status', 'teacher_subject_timetable_id')
                    ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                    ->whereBetween('date', [$startOfMonth, $endOfMonth])
                    ->where('teacher_subject_timetable_id', $lastTimetable->teacher_subject_timetable_id) // Lọc theo tiết học cuối cùng
                    ->where('status', StatusStudentEnum::PRESENT->value)
                    ->orderBy('student_id') // Đảm bảo nhóm theo student_id
                    ->orderBy('created_at', 'desc') // Lấy bản ghi mới nhất
                    ->get()
                    ->unique('student_id'); // Loại bỏ bản ghi trùng `student_id`

                // Đếm tổng số học sinh có mặt trong tiết học cuối cùng
                $totalAttendances += $allAtten->count();
            }

            // Đếm số học sinh không tham gia trong tiết học cuối cùng
            $allUnattend += StatisAttendance::select('student_id', 'teacher_subject_timetable_id')
                ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->where('status', StatusStudentEnum::UN_PRESENT->value)
                ->where('teacher_subject_timetable_id', $lastTimetable ? $lastTimetable->teacher_subject_timetable_id : null) // Lọc theo tiết học cuối cùng
                ->count();

            // Đếm số học sinh không tham gia tạm thời trong tiết học cuối cùng
            $allunpresentper += StatisAttendance::select('student_id', 'teacher_subject_timetable_id')
                ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->where('status', StatusStudentEnum::UN_PRESENT_PER->value)
                ->where('teacher_subject_timetable_id', $lastTimetable ? $lastTimetable->teacher_subject_timetable_id : null) // Lọc theo tiết học cuối cùng
                ->count();

            // Đếm số học sinh đi muộn trong tiết học cuối cùng
            $allLate += StatisAttendance::select('student_id', 'teacher_subject_timetable_id')
                ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])
                ->where('status', StatusStudentEnum::LATE->value)
                ->where('teacher_subject_timetable_id', $lastTimetable ? $lastTimetable->teacher_subject_timetable_id : null) // Lọc theo tiết học cuối cùng
                ->count();
        }

        return [
            'allAttendances' => $totalAttendances, // Tổng số học sinh có mặt trong tiết học cuối cùng
            'allUnattend' => $allUnattend, // Số học sinh vắng mặt không phép
            'allunpresentper' => $allunpresentper, // Số học sinh vắng mặt có phép
            'allLate' => $allLate, // Số học sinh đi muộn
        ];
    }

    public function getStatisAttendanceSchoolOnDayWithClass()
    {
        $today = now(); // Lấy ngày hiện tại
        $classes = Classes::where('is_deleted', DeleteEnum::NOT_DELETE->value)->get(); // Lấy tất cả các lớp không bị xóa

        // Khởi tạo một mảng để lưu kết quả
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
                    return $attendanceRecords->sortByDesc('time')->first(); // Sắp xếp theo thời gian và lấy bản ghi mới nhất
                });

            // Lấy học sinh vắng mặt không phép
            $allUnattend = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereDate('date', $today)  // So sánh ngày
                ->where('class_id', $class->id)
                ->where('status', StatusStudentEnum::UN_PRESENT->value)  // Trạng thái 'Vắng mặt không phép'
                ->get()
                ->groupBy('student_id')  // Nhóm theo student_id
                ->map(function ($attendanceRecords) {
                    // Lấy bản ghi mới nhất của mỗi học sinh
                    return $attendanceRecords->sortByDesc('time')->first(); // Sắp xếp theo thời gian và lấy bản ghi mới nhất
                });

            // Lấy học sinh vắng mặt có phép
            $allUnpresentper = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereDate('date', $today)  // So sánh ngày
                ->where('class_id', $class->id)
                ->where('status', StatusStudentEnum::UN_PRESENT_PER->value)  // Trạng thái 'Vắng mặt có phép'
                ->get()
                ->groupBy('student_id')  // Nhóm theo student_id
                ->map(function ($attendanceRecords) {
                    // Lấy bản ghi mới nhất của mỗi học sinh
                    return $attendanceRecords->sortByDesc('time')->first(); // Sắp xếp theo thời gian và lấy bản ghi mới nhất
                });

            // Lấy học sinh đi muộn
            $allLate = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereDate('date', $today)  // So sánh ngày
                ->where('class_id', $class->id)
                ->where('status', StatusStudentEnum::LATE->value)  // Trạng thái 'Đi muộn'
                ->get()
                ->groupBy('student_id')  // Nhóm theo student_id
                ->map(function ($attendanceRecords) {
                    // Lấy bản ghi mới nhất của mỗi học sinh
                    return $attendanceRecords->sortByDesc('time')->first(); // Sắp xếp theo thời gian và lấy bản ghi mới nhất
                });

            // Đếm số lượng học sinh có mặt, vắng mặt, đi muộn trong lớp
            $allAtten = $studentsAttendance->count();
            $allUnattend = $allUnattend->count();
            $allUnpresentper = $allUnpresentper->count();
            $allLate = $allLate->count();

            return [
                'className' => $class->name, // Tên lớp
                'allAttendances' => $allAtten, // Số học sinh có mặt trong lớp
                'allUnattend' => $allUnattend, // Số học sinh vắng mặt không phép
                'unpresentper' => $allUnpresentper, // Số học sinh vắng mặt có phép
                'allLate' => $allLate, // Số học sinh đi muộn
            ];
        });

        return $statistics;
    }



    public function getStatisAttendanceSchoolOnWeekWithClass()
    {
        $startOfWeek = Carbon::now()->startOfWeek(); // Lấy ngày đầu tuần (thứ 2)
        $endOfWeek = Carbon::now()->endOfWeek();     // Lấy ngày cuối tuần (chủ nhật)
        $classes = Classes::where('is_deleted', DeleteEnum::NOT_DELETE->value)->get(); // Lấy tất cả các lớp không bị xóa

        // Khởi tạo một mảng để lưu kết quả
        $statistics = $classes->map(function ($class) use ($startOfWeek, $endOfWeek) {
            // Lấy điểm danh mới nhất của từng học sinh trong lớp trong tuần này
            $studentsAttendance = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereBetween('date', [$startOfWeek, $endOfWeek])  // So sánh ngày trong tuần
                ->where('class_id', $class->id)
                ->where('status', StatusStudentEnum::PRESENT->value)  // Trạng thái 'Có mặt'
                ->get()
                ->groupBy('student_id')  // Nhóm theo student_id
                ->map(function ($attendanceRecords) {
                    // Lấy bản ghi mới nhất của mỗi học sinh
                    return $attendanceRecords->sortByDesc('time')->first(); // Sắp xếp theo thời gian và lấy bản ghi mới nhất
                });

            // Lấy học sinh vắng mặt không phép
            $allUnattend = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereBetween('date', [$startOfWeek, $endOfWeek])  // So sánh ngày trong tuần
                ->where('class_id', $class->id)
                ->where('status', StatusStudentEnum::UN_PRESENT->value)  // Trạng thái 'Vắng mặt không phép'
                ->get()
                ->groupBy('student_id')  // Nhóm theo student_id
                ->map(function ($attendanceRecords) {
                    // Lấy bản ghi mới nhất của mỗi học sinh
                    return $attendanceRecords->sortByDesc('time')->first(); // Sắp xếp theo thời gian và lấy bản ghi mới nhất
                });

            // Lấy học sinh vắng mặt có phép
            $allUnpresentper = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereBetween('date', [$startOfWeek, $endOfWeek])  // So sánh ngày trong tuần
                ->where('class_id', $class->id)
                ->where('status', StatusStudentEnum::UN_PRESENT_PER->value)  // Trạng thái 'Vắng mặt có phép'
                ->get()
                ->groupBy('student_id')  // Nhóm theo student_id
                ->map(function ($attendanceRecords) {
                    // Lấy bản ghi mới nhất của mỗi học sinh
                    return $attendanceRecords->sortByDesc('time')->first(); // Sắp xếp theo thời gian và lấy bản ghi mới nhất
                });

            // Lấy học sinh đi muộn
            $allLate = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereBetween('date', [$startOfWeek, $endOfWeek])  // So sánh ngày trong tuần
                ->where('class_id', $class->id)
                ->where('status', StatusStudentEnum::LATE->value)  // Trạng thái 'Đi muộn'
                ->get()
                ->groupBy('student_id')  // Nhóm theo student_id
                ->map(function ($attendanceRecords) {
                    // Lấy bản ghi mới nhất của mỗi học sinh
                    return $attendanceRecords->sortByDesc('time')->first(); // Sắp xếp theo thời gian và lấy bản ghi mới nhất
                });

            // Đếm số lượng học sinh có mặt, vắng mặt, đi muộn trong lớp
            $allAtten = $studentsAttendance->count();
            $allUnattend = $allUnattend->count();
            $allUnpresentper = $allUnpresentper->count();
            $allLate = $allLate->count();

            return [
                'className' => $class->name, // Tên lớp
                'allAttendances' => $allAtten, // Số học sinh có mặt trong lớp
                'allUnattend' => $allUnattend, // Số học sinh vắng mặt không phép
                'unpresentper' => $allUnpresentper, // Số học sinh vắng mặt có phép
                'allLate' => $allLate, // Số học sinh đi muộn
            ];
        });

        return $statistics;
    }


    public function getStatisAttendanceSchoolOnMonthWithClass()
    {
        $startOfMonth = Carbon::now()->startOfMonth(); // Lấy ngày đầu tháng
        $endOfMonth = Carbon::now()->endOfMonth();     // Lấy ngày cuối tháng
        $classes = Classes::where('is_deleted', DeleteEnum::NOT_DELETE->value)->get(); // Lấy tất cả các lớp không bị xóa

        // Khởi tạo một mảng để lưu kết quả
        $statistics = $classes->map(function ($class) use ($startOfMonth, $endOfMonth) {
            // Lấy điểm danh mới nhất của từng học sinh trong lớp trong tháng này
            $studentsAttendance = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])  // So sánh ngày trong tháng
                ->where('class_id', $class->id)
                ->where('status', StatusStudentEnum::PRESENT->value)  // Trạng thái 'Có mặt'
                ->get()
                ->groupBy('student_id')  // Nhóm theo student_id
                ->map(function ($attendanceRecords) {
                    // Lấy bản ghi mới nhất của mỗi học sinh
                    return $attendanceRecords->sortByDesc('time')->first(); // Sắp xếp theo thời gian và lấy bản ghi mới nhất
                });

            // Lấy học sinh vắng mặt không phép
            $allUnattend = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])  // So sánh ngày trong tháng
                ->where('class_id', $class->id)
                ->where('status', StatusStudentEnum::UN_PRESENT->value)  // Trạng thái 'Vắng mặt không phép'
                ->get()
                ->groupBy('student_id')  // Nhóm theo student_id
                ->map(function ($attendanceRecords) {
                    // Lấy bản ghi mới nhất của mỗi học sinh
                    return $attendanceRecords->sortByDesc('time')->first(); // Sắp xếp theo thời gian và lấy bản ghi mới nhất
                });

            // Lấy học sinh vắng mặt có phép
            $allUnpresentper = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])  // So sánh ngày trong tháng
                ->where('class_id', $class->id)
                ->where('status', StatusStudentEnum::UN_PRESENT_PER->value)  // Trạng thái 'Vắng mặt có phép'
                ->get()
                ->groupBy('student_id')  // Nhóm theo student_id
                ->map(function ($attendanceRecords) {
                    // Lấy bản ghi mới nhất của mỗi học sinh
                    return $attendanceRecords->sortByDesc('time')->first(); // Sắp xếp theo thời gian và lấy bản ghi mới nhất
                });

            // Lấy học sinh đi muộn
            $allLate = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereBetween('date', [$startOfMonth, $endOfMonth])  // So sánh ngày trong tháng
                ->where('class_id', $class->id)
                ->where('status', StatusStudentEnum::LATE->value)  // Trạng thái 'Đi muộn'
                ->get()
                ->groupBy('student_id')  // Nhóm theo student_id
                ->map(function ($attendanceRecords) {
                    // Lấy bản ghi mới nhất của mỗi học sinh
                    return $attendanceRecords->sortByDesc('time')->first(); // Sắp xếp theo thời gian và lấy bản ghi mới nhất
                });

            // Đếm số lượng học sinh có mặt, vắng mặt, đi muộn trong lớp
            $allAtten = $studentsAttendance->count();
            $allUnattend = $allUnattend->count();
            $allUnpresentper = $allUnpresentper->count();
            $allLate = $allLate->count();

            return [
                'className' => $class->name, // Tên lớp
                'allAttendances' => $allAtten, // Số học sinh có mặt trong lớp
                'allUnattend' => $allUnattend, // Số học sinh vắng mặt không phép
                'unpresentper' => $allUnpresentper, // Số học sinh vắng mặt có phép
                'allLate' => $allLate, // Số học sinh đi muộn
            ];
        });

        return $statistics;
    }
}
