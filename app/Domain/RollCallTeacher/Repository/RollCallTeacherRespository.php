<?php

namespace App\Domain\RollCallTeacher\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\statusClassAttendance;
use App\Common\Enums\StatusStudentEnum;
use App\Domain\RollCallTeacher\Models\RollCallTeacher;
use App\Models\Classes;
use App\Models\StudentClassHistory;
use Carbon\Carbon;

class RollCallTeacherRespository
{
    public function getClass($pageIndex, $pageSize, $keyWord = null, $date = null)
    {
        // Đếm số lớp đã điểm danh và chưa điểm danh
        $totalClassAttendanced = StudentClassHistory::where('status', StatusClassAttendance::HAS_CHECKED->value)
            ->distinct('class_id')
            ->count('class_id');

        $totalClassNoAttendance = StudentClassHistory::where('status', StatusClassAttendance::NOT_YET_CHECKED->value)
            ->distinct('class_id')
            ->count();

        // Lấy danh sách các lớp học
        $classesQuery = Classes::with(['user', 'rollCalls']);

        // Tìm kiếm theo từ khóa nếu có
        if ($keyWord) {
            $classesQuery->where('name', 'LIKE', '%' . $keyWord . '%');
        }

        // Lọc theo ngày nếu có
        if ($date) {
            $classesQuery->whereHas('rollCalls', function ($query) use ($date) {
                $query->whereDate('date', $date);
            });
        }

        // Phân trang
        $classes = $classesQuery->paginate($pageSize, ['*'], 'page', $pageIndex);

        // Duyệt qua các lớp và cấu trúc dữ liệu trả về
        $data = $classes->map(function ($class) {
            // Lấy giáo viên chủ nhiệm
            $teacher = optional($class->user);

            // Đếm số lượng học sinh liên kết với lớp
            $totalStudent = StudentClassHistory::where('class_id', $class->id)
                ->where('is_deleted', DeleteEnum::NOT_DELETE->value) // Loại trừ bản ghi đã bị xóa
                ->count();

            // Lấy thông tin điểm danh (rollCalls) của lớp
            $rollCall = optional($class->rollCalls)->first(); // Lấy thông tin điểm danh đầu tiên nếu có

            // Đếm số học sinh có mặt
            $studentAttendanced = RollCallTeacher::where('class_id', $class->id)
                ->where('status', StatusStudentEnum::PRESENT->value) // Lọc theo trạng thái học sinh có mặt
                ->count();

            return [
                'classId' => $class->id,
                'className' => $class->name,
                'grade' => optional($class->grade)->name,
                'totalStudent' => $totalStudent,
                'dateAttendanced' => optional($rollCall)->date,
                'attendanceAt' => optional($rollCall) ? strtotime($rollCall->time) : null,
                'fullname' => optional($teacher)->fullname,
                'email' => optional($teacher)->email,
                'status' => optional($class)->status,
                'studentAttendanced' => $studentAttendanced,
                'attendanceBy' => optional($rollCall)->created_user_id,
            ];
        });

        // Trả về kết quả
        return [
            'totalClassAttendanced' => $totalClassAttendanced,
            'totalClassNoAttendance' => $totalClassNoAttendance,
            'data' => $data,
            'total' => $classes->total(),
        ];
    }

    public function getStudentClassDetails($classId, $rollCallData = [], $user_id)
    {
        // Đếm tổng số học sinh trong lớp
        $totalStudent = StudentClassHistory::where('class_id', $classId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->count();

        $totalStudentNotAttendaced = RollCallTeacher::where('status', StatusStudentEnum::UN_PRESENT)
            ->where('class_id', $classId)
            ->count();

        $totalStudentAttendaced = RollCallTeacher::where('status', StatusStudentEnum::PRESENT)
            ->where('class_id', $classId)
            ->count();

        // Lấy tất cả học sinh trong lớp
        $studentClasses = StudentClassHistory::with(['student'])
            ->where('class_id', $classId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get();

        // Danh sách roll call mới được thêm vào
        $insertedRollCalls = [];

        // Xử lý điểm danh nếu có dữ liệu
        if (!empty($rollCallData)) {
            foreach ($studentClasses as $studentClass) {
                if (isset($rollCallData[$studentClass->student_id])) {
                    // Ghi dữ liệu điểm danh
                    $rollCall = RollCallTeacher::create([
                        'student_id' => $studentClass->student_id,
                        'class_id' => $classId,
                        'status' => $rollCallData[$studentClass->student_id]['status'],
                        'date' => now()->format('Y-m-d'),
                        'time' => now()->format('H:i:s'),
                        'note' => $rollCallData[$studentClass->student_id]['note'] ?? null,
                        'created_user_id' => $user_id,
                    ]);

                    // Thêm vào danh sách roll call mới
                    $insertedRollCalls[] = [
                        'className' => $studentClass->class->name ?? 'N/A',
                        'fullname' => $studentClass->student->fullname ?? 'N/A',
                        'studentDOB' => $studentClass->student->dob ?? 'N/A',
                        'note' => $rollCall->note ?? 'N/A',
                        'status' => $rollCall->status ?? 'N/A',
                    ];
                }
            }
        }

        return [
            'totalStudent' => $totalStudent,
            'totalStudentNotAttendaced' => $totalStudentNotAttendaced,
            'totalStudentAttendaced' => $totalStudentAttendaced,
            'insert_roll_call' => $insertedRollCalls, // Chỉ trả về các bản ghi đã thêm
        ];
    }




    public function updateByClass($class_id, $studentsData, $user_id)
    {
        // Đếm tổng số học sinh trong lớp
        $totalStudent = StudentClassHistory::where('class_id', $class_id)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->count();

        $totalStudentNotAttendaced = RollCallTeacher::where('status', StatusStudentEnum::UN_PRESENT)
            ->where('class_id', $class_id)
            ->count();

        $totalStudentAttendaced = RollCallTeacher::where('status', StatusStudentEnum::PRESENT)
            ->where('class_id', $class_id)
            ->count();

        // Danh sách bản ghi đã cập nhật
        $updatedRollCalls = [];

        // Cập nhật trạng thái cho từng học sinh
        foreach ($studentsData as $student) {
            // Tìm bản ghi roll_call theo class_id và student_id
            $rollCall = RollCallTeacher::with('student', 'class')->where('class_id', $class_id)
                ->where('student_id', $student['student_id'])
                ->first();

            if ($rollCall) {
                // Cập nhật các trường time, note, status
                $rollCall->update([
                    'time' => now(),
                    'note' => $student['note'],
                    'status' => $student['status'],
                    'modified_user_id' => $user_id,
                ]);
                // Thêm thông tin bản ghi đã cập nhật vào danh sách
                $updatedRollCalls[] = [
                    'fullname' => $rollCall->student->fullname,
                    'dob' => $rollCall->student->dob,
                    'note' => $rollCall->note,
                    'status' => $rollCall->status,
                ];
            }
        }

        return [
            $totalStudent,
            $totalStudentAttendaced,
            $totalStudentNotAttendaced,
            $updatedRollCalls // Trả về danh sách bản ghi đã cập nhật
        ];
    }
}