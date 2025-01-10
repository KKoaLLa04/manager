<?php

namespace App\Domain\StatisAttendance\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusStudentEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\StatisAttendance\Models\StatisAttendance;
use App\Models\Classes;
use App\Models\ClassSubjectTeacher;
use Carbon\Carbon;

class StatisAttendanceTeacherResponsitory
{
    public function getAllAttendanceTeachersOnDay($teacherId)
    {
        $today = now()->toDateString(); // Lấy ngày hiện tại

        // Lấy danh sách tất cả các lớp mà giáo viên dạy
        $classes = ClassSubjectTeacher::with('class') // Quan hệ với bảng lớp
            ->where('user_id', $teacherId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('status', StatusEnum::ACTIVE->value)
            ->get()
            ->map(function ($item) {
                return [
                    'class_id' => $item->class_id,
                    'class_name' => $item->class->name ?? 'Không xác định', // Tên lớp
                    'teacher_type' => $item->access_type == StatusTeacherEnum::MAIN_TEACHER->value
                        ? StatusTeacherEnum::MAIN_TEACHER->value
                        : StatusTeacherEnum::TEACHER->value,
                ];
            });

        // Khởi tạo danh sách kết quả
        $result = [];

        foreach ($classes as $class) {
            // Lấy tiết học cuối cùng trong ngày của lớp hiện tại
            $lastTimetable = StatisAttendance::select('teacher_subject_timetable_id')
                ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereDate('date', $today)
                ->where('class_id', $class['class_id']) // Lọc theo lớp hiện tại
                ->distinct() // Lọc ra các tiết học
                ->latest('created_at') // Lấy tiết học cuối cùng
                ->first();

            if ($lastTimetable) {
                $lastTimetableId = $lastTimetable->teacher_subject_timetable_id;

                // Đếm học sinh có mặt
                $totalAttendances = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                    ->whereDate('date', $today)
                    ->where('teacher_subject_timetable_id', $lastTimetableId)
                    ->where('status', StatusStudentEnum::PRESENT->value)
                    ->distinct('student_id') // Loại bỏ trùng lặp
                    ->count('student_id');

                // Đếm học sinh vắng mặt không phép
                $allUnattend = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                    ->whereDate('date', $today)
                    ->where('teacher_subject_timetable_id', $lastTimetableId)
                    ->where('status', StatusStudentEnum::UN_PRESENT->value)
                    ->distinct('student_id')
                    ->count('student_id');

                // Đếm học sinh vắng mặt có phép
                $allunpresentper = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                    ->whereDate('date', $today)
                    ->where('teacher_subject_timetable_id', $lastTimetableId)
                    ->where('status', StatusStudentEnum::UN_PRESENT_PER->value)
                    ->distinct('student_id')
                    ->count('student_id');

                // Đếm học sinh đi muộn
                $allLate = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                    ->whereDate('date', $today)
                    ->where('teacher_subject_timetable_id', $lastTimetableId)
                    ->where('status', StatusStudentEnum::LATE->value)
                    ->distinct('student_id')
                    ->count('student_id');
            } else {
                // Nếu không có tiết học, đặt tất cả số liệu bằng 0
                $totalAttendances = 0;
                $allUnattend = 0;
                $allunpresentper = 0;
                $allLate = 0;
            }

            // Thêm thông tin vào danh sách kết quả
            $result[] = [
                'class_name' => $class['class_name'],
                'teacher_type' => $class['teacher_type'],
                'allAttendances' => $totalAttendances,
                'allUnattend' => $allUnattend,
                'allunpresentper' => $allunpresentper,
                'allLate' => $allLate,
            ];
        }

        return $result;
    }

    public function getAllAttendanceTeachersOnWeek($teacherId)
    {
        $startOfWeek = now()->startOfWeek()->toDateString(); // Ngày đầu tuần (Thứ Hai)
        $endOfWeek = now()->endOfWeek()->toDateString();     // Ngày cuối tuần (Chủ Nhật)

        // Lấy danh sách tất cả các lớp mà giáo viên dạy
        $classes = ClassSubjectTeacher::with('class') // Quan hệ với bảng lớp
            ->where('user_id', $teacherId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('status', StatusEnum::ACTIVE->value)
            ->get()
            ->map(function ($item) {
                return [
                    'class_id' => $item->class_id,
                    'class_name' => $item->class->name ?? 'Không xác định', // Tên lớp
                    'teacher_type' => $item->access_type == StatusTeacherEnum::MAIN_TEACHER->value
                        ? StatusTeacherEnum::MAIN_TEACHER->value
                        : StatusTeacherEnum::TEACHER->value,
                ];
            });

        $result = [];

        foreach ($classes as $class) {
            // Lấy danh sách tiết học trong tuần
            $weeklyAttendance = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereBetween('date', [$startOfWeek, $endOfWeek]) // Lọc theo tuần
                ->where('class_id', $class['class_id']) // Lọc theo lớp hiện tại
                ->distinct('teacher_subject_timetable_id') // Lấy các tiết học không trùng lặp
                ->get();

            // Tính toán thống kê
            $totalAttendances = 0;
            $totalUnattend = 0;
            $totalUnpresentPer = 0;
            $totalLate = 0;

            foreach ($weeklyAttendance as $attendance) {
                $timetableId = $attendance->teacher_subject_timetable_id;

                // Đếm số lượng học sinh trong từng trạng thái
                $totalAttendances += StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                    ->where('teacher_subject_timetable_id', $timetableId)
                    ->where('status', StatusStudentEnum::PRESENT->value)
                    ->distinct('student_id')
                    ->count('student_id');

                $totalUnattend += StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                    ->where('teacher_subject_timetable_id', $timetableId)
                    ->where('status', StatusStudentEnum::UN_PRESENT->value)
                    ->distinct('student_id')
                    ->count('student_id');

                $totalUnpresentPer += StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                    ->where('teacher_subject_timetable_id', $timetableId)
                    ->where('status', StatusStudentEnum::UN_PRESENT_PER->value)
                    ->distinct('student_id')
                    ->count('student_id');

                $totalLate += StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                    ->where('teacher_subject_timetable_id', $timetableId)
                    ->where('status', StatusStudentEnum::LATE->value)
                    ->distinct('student_id')
                    ->count('student_id');
            }

            $result[] = [
                'class_name' => $class['class_name'],
                'teacher_type' => $class['teacher_type'],
                'allAttendances' => $totalAttendances,
                'allUnattend' => $totalUnattend,
                'allUnpresentPer' => $totalUnpresentPer,
                'allLate' => $totalLate,
            ];
        }

        return $result;
    }

    public function getAllAttendanceTeachersOnMonth($teacherId)
    {
        $startOfMonth = now()->startOfMonth()->toDateString(); // Ngày đầu tháng
        $endOfMonth = now()->endOfMonth()->toDateString();     // Ngày cuối tháng

        // Lấy danh sách tất cả các lớp mà giáo viên dạy
        $classes = ClassSubjectTeacher::with('class') // Quan hệ với bảng lớp
            ->where('user_id', $teacherId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('status', StatusEnum::ACTIVE->value)
            ->get()
            ->map(function ($item) {
                return [
                    'class_id' => $item->class_id,
                    'class_name' => $item->class->name ?? 'Không xác định', // Tên lớp
                    'teacher_type' => $item->access_type == StatusTeacherEnum::MAIN_TEACHER->value
                        ? StatusTeacherEnum::MAIN_TEACHER->value
                        : StatusTeacherEnum::TEACHER->value,
                ];
            });

        $result = [];

        foreach ($classes as $class) {
            // Lấy danh sách tiết học trong tháng
            $monthlyAttendance = StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereBetween('date', [$startOfMonth, $endOfMonth]) // Lọc theo tháng
                ->where('class_id', $class['class_id']) // Lọc theo lớp hiện tại
                ->distinct('teacher_subject_timetable_id') // Lấy các tiết học không trùng lặp
                ->get();

            // Tính toán thống kê
            $totalAttendances = 0;
            $totalUnattend = 0;
            $totalUnpresentPer = 0;
            $totalLate = 0;

            foreach ($monthlyAttendance as $attendance) {
                $timetableId = $attendance->teacher_subject_timetable_id;

                // Đếm số lượng học sinh trong từng trạng thái
                $totalAttendances += StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                    ->where('teacher_subject_timetable_id', $timetableId)
                    ->where('status', StatusStudentEnum::PRESENT->value)
                    ->distinct('student_id')
                    ->count('student_id');

                $totalUnattend += StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                    ->where('teacher_subject_timetable_id', $timetableId)
                    ->where('status', StatusStudentEnum::UN_PRESENT->value)
                    ->distinct('student_id')
                    ->count('student_id');

                $totalUnpresentPer += StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                    ->where('teacher_subject_timetable_id', $timetableId)
                    ->where('status', StatusStudentEnum::UN_PRESENT_PER->value)
                    ->distinct('student_id')
                    ->count('student_id');

                $totalLate += StatisAttendance::where('is_deleted', DeleteEnum::NOT_DELETE->value)
                    ->where('teacher_subject_timetable_id', $timetableId)
                    ->where('status', StatusStudentEnum::LATE->value)
                    ->distinct('student_id')
                    ->count('student_id');
            }

            $result[] = [
                'class_name' => $class['class_name'],
                'teacher_type' => $class['teacher_type'],
                'allAttendances' => $totalAttendances,
                'allUnattend' => $totalUnattend,
                'allUnpresentPer' => $totalUnpresentPer,
                'allLate' => $totalLate,
            ];
        }

        return $result;
    }
}
