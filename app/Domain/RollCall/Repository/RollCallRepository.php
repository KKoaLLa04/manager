<?php

namespace App\Domain\RollCall\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\statusClassAttendance;
use App\Common\Enums\StatusClassStudentEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusStudentEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\RollCall\Models\RollCall;
use App\Domain\RollCallHistory\Models\RollCallHistory;
use App\Jobs\CreateNotification;
use App\jobs\NotificationJob;
use App\Models\AttendanceLog;
use App\Models\Classes;
use App\Models\Student;
use App\Models\StudentClassHistory;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class RollCallRepository
{
    public function getClass($pageIndex = 1, $pageSize = 10, $keyWord = null, $date = null)
{
    // Đếm số lớp đã điểm danh và chưa điểm danh
    $totalClassAttendanced = StudentClassHistory::where('status', StatusClassAttendance::HAS_CHECKED->value)
        ->distinct('class_id')
        ->count('class_id');

    $totalClassNoAttendance = StudentClassHistory::where('status', StatusClassAttendance::NOT_YET_CHECKED->value)
        ->distinct('class_id')
        ->count();

    // Truy vấn danh sách các lớp học
    $classesQuery = Classes::with(['user', 'rollCalls.attendanceBy']);  // eager load attendanceBy

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

    // Xử lý dữ liệu trả về
    $data = $classes->map(function ($class) {
        $teacher = optional($class->user->first());  // Lấy giáo viên dạy lớp
        
        // Đếm tổng số học sinh trong lớp
        $totalStudent = StudentClassHistory::where('class_id', $class->id)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->count();
    
        // Lấy lần điểm danh đầu tiên
        $rollCall = optional($class->rollCalls)->first();
    
        // Lấy tên giáo viên điểm danh từ quan hệ attendanceBy
        $attendanceBy = optional($class->rollCalls)->first()->attendanceBy->fullname ?? null;
        
    
        // Đếm số học sinh đã điểm danh
        $studentAttendanced = RollCall::where('class_id', $class->id)
            ->where('status', StatusStudentEnum::PRESENT->value)
            ->count();
    
        // Thời gian điểm danh và ngày điểm danh
        $attendanceAt = optional($rollCall)->time ? strtotime($rollCall->time) : null;
        $dateAttendanced = optional($rollCall)->date ? Carbon::parse($rollCall->date)->translatedFormat('l, d/m/Y') : null;
    
        return [
            'classId' => $class->id,
            'className' => $class->name,
            'grade' => optional($class->grade)->name,
            'totalStudent' => $totalStudent,
            'dateAttendanced' => $dateAttendanced,
            'attendanceAt' => $attendanceAt,
            'fullname' => $teacher->fullname,
            'email' => $teacher->email,
            'status' => $class->status,
            'studentAttendanced' => $studentAttendanced,
            'attendanceBy' => $attendanceBy,  // Trả về tên giáo viên điểm danh
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


    public function attendanceStudentOfClass($classId, $rollCallData = [], $user_id, Carbon $date)
    {
        // Đếm tổng số học sinh trong lớp
        $studentIds = StudentClassHistory::where('class_id', $classId)
            ->whereNull('end_date')
            ->where('status', StatusClassStudentEnum::STUDYING->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get()->pluck('student_id')->toArray();
        $studentRecords = RollCall::query()
            ->where('class_id', $classId)
            ->where('date', $date->toDateString())
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get()->keyBy('student_id');

        // Lấy tất cả học sinh trong lớp
        $rollCallData = collect($rollCallData)->whereIn('studentID',$studentIds);
        $dataInsertRollCallHistory = [];
        foreach ($rollCallData as $data){
            $rollCall = $studentRecords->get($data['studentID']);

            if(!is_null($rollCall)){
                $dataUpdate = [
                    "student_id" => $data['studentID'],
                    "note" => $data['note'],
                    "class_id" => $classId,
                    "date" => $date->toDateString(),
                    "time" => now()->toTimeString(),
                    "status" => $data['status'],
                    "modified_user_id" => $user_id,
                ];
                RollCall::query()->where('id', $rollCall->id)->update($dataUpdate);
                $dataInsertRollCallHistory [] = [
                    "student_id" => $data['studentID'],
                    "note" => $data['note'],
                    "class_id" => $classId,
                    "roll_call_id" => $rollCall->id,
                    "date" => $date->toDateString(),
                    "time" => now()->toTimeString(),
                    "status" => $data['status'],
                    "user_id" => $user_id,
                    "created_at" => now(),
                    "updated_at" => now(),
                ];

            }else{
                $dataInsert = [
                    "student_id" => $data['studentID'],
                    "note" => $data['note'],
                    "class_id" => $classId,
                    "date" => $date->toDateString(),
                    "time" => now()->toTimeString(),
                    "status" => $data['status'],
                    "created_user_id" => $user_id,
                ];
                $rollCall = RollCall::query()->create($dataInsert);
                CreateNotification::dispatch($rollCall);

                $dataInsertRollCallHistory [] = [
                    "student_id" => $data['studentID'],
                    "note" => $data['note'],
                    "class_id" => $classId,
                    "roll_call_id" => $rollCall->id,
                    "date" => $date->toDateString(),
                    "time" => now()->toTimeString(),
                    "status" => $data['status'],
                    "user_id" => $user_id,
                    "created_at" => now(),
                    "updated_at" => now(),
                ];
            }
        }
        RollCallHistory::query()->insert($dataInsertRollCallHistory);

    }

    private function attendanceLog($classId)
    {
        AttendanceLog::query()->create(
            [
                'user_id'  => Auth::id(),
                'class_id' => $classId,
                'date'     => now()->toDateString(),
            ]
        );
    }


    public function updateByClass($class_id, $studentsData, $user_id)
    {
        // Đếm tổng số học sinh trong lớp
        $totalStudent = StudentClassHistory::where('class_id', $class_id)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->count();

        $totalStudentNotAttendaced = RollCall::where('status', StatusStudentEnum::UN_PRESENT)
            ->where('class_id', $class_id)
            ->count();

        $totalStudentAttendaced = RollCall::where('status', StatusStudentEnum::PRESENT)
            ->where('class_id', $class_id)
            ->count();

        // Danh sách bản ghi đã cập nhật
        $updatedRollCalls = [];

        // Cập nhật trạng thái cho từng học sinh
        foreach ($studentsData as $student) {
            // Tìm bản ghi roll_call theo class_id và student_id
            $rollCall = RollCall::with('student', 'class')->where('class_id', $class_id)
                ->where('student_id', $student['student_id'])
                ->first();

            if ($rollCall) {
                // Cập nhật các trường time, note, status
                $rollCall->update([
                    'time'             => now(),
                    'note'             => $student['note'],
                    'status'           => $student['status'],
                    'modified_user_id' => $user_id,
                ]);
                CreateNotification::dispatch($rollCall);
                // Thêm thông tin bản ghi đã cập nhật vào danh sách
                $updatedRollCalls[] = [
                    'fullname' => $rollCall->student->fullname,
                    'dob'      => $rollCall->student->dob,
                    'note'     => $rollCall->note,
                    'status'   => $rollCall->status,
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

    public function getStudentClass(int $classId, string $keyWord = null): array
    {
        $studentIds = StudentClassHistory::query()
            ->where('class_id', $classId)
            ->whereNull('end_date')
            ->where('status', StatusClassStudentEnum::STUDYING->value)
            ->get()->pluck('student_id')->toArray();

        $query = Student::query()
            ->whereIn('id', $studentIds)
        ->where('is_deleted', DeleteEnum::NOT_DELETE->value);
        if(!is_null($keyWord)){
            $query->where('fullname', 'like', '%'.$keyWord.'%');
        }
        return [$query->get(), count($studentIds)];
    }

    public function getRollCall(int $classId, Collection $students, Carbon $date): array
    {
        $studentIds = $students->pluck('id')->toArray();
        $rollCalls  = RollCall::query()
            ->where('class_id', $classId)
            ->whereIn('student_id', $studentIds)
            ->where('date', $date->toDateString())
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get();
        return $students->map(function ($student) use ($rollCalls) {
            $status   = StatusStudentEnum::PRESENT->value;
            $note     = "";
            $rollCall = $rollCalls->where('student_id', $student->id)->first();
            if (!is_null($rollCall)) {
                $status = $rollCall->status;
                $note   = $rollCall->note;
            }
            return [
                'id'       => $student->id,
                'fullname' => $student->fullname ?? "",
                'code'     => $student->student_code ?? "",
                'dob'      => $student->dob ?? "",
                'status'   => $status,
                'note'     => $note,
            ];
        })->toArray();
    }
}
