<?php

namespace App\Domain\RollCall\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\GenderEnum;
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
use App\Models\DiemDanh;
use App\Models\Student;
use App\Models\StudentClassHistory;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class RollCallRepository
{
    public function getClass($pageIndex = 1, $pageSize = 10, $keyWord = null, $date = null)
    {
        // Nếu không có ngày, lấy ngày hiện tại
        $date = $date ?? Carbon::now()->toDateString();


        // Truy vấn các lớp (bao gồm cả lớp đã có điểm danh trước đó)
        $query = Classes::where('is_deleted', DeleteEnum::NOT_DELETE->value)
            // Lọc các lớp có ít nhất một học sinh trong classHistory
            ->whereHas('classHistory', function ($query) {
                $query->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                    ->where('status', StatusEnum::ACTIVE->value); // Lọc các học sinh còn lại
            })
            ->with([
                'grade',
                'classHistory' => function ($query) use ($date) {
                    $query->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                        ->where('status', StatusEnum::ACTIVE->value)
                        ->whereDate('start_date', '<=', $date); // Kiểm tra lớp đã bắt đầu

                },
                'rollCalls'    => function ($query) use ($date) {
                    // Lọc điểm danh theo ngày, kể cả ngày trong quá khứ
                    $query->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                        ->whereDate('date', $date); // Lọc các lớp có điểm danh trong ngày
                }
            ]);

        // Lọc theo từ khóa nếu có
        if ($keyWord) {
            $query->where(function ($q) use ($keyWord) {
                $q->where('name', 'LIKE', '%'.$keyWord.'%')
                    ->orWhereHas('grade', function ($q) use ($keyWord) {
                        $q->where('name', 'LIKE', '%'.$keyWord.'%');
                    });
            });
        }

        // Lấy các lớp với phân trang
        $classes = $query->paginate($pageSize, ['*'], 'page', $pageIndex);
        // Tổng số lớp đã điểm danh trong ngày hôm nay
        $totalClassAttendanced = Classes::whereHas('attendanceLog', function ($query) {
            $query->where('is_deleted', DeleteEnum::NOT_DELETE->value);
        })
            ->whereHas('rollCalls', function ($subQuery) use ($date) {
                $subQuery->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                    ->whereDate('date', $date); // Lọc theo ngày hôm nay
            })
            ->count();

        // Tổng số lớp chưa điểm danh trong ngày hôm nay
        $totalClassNoAttendance = Classes::whereHas('classHistory', function ($query) {
            $query->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->where('status', StatusEnum::ACTIVE->value); // Lọc lớp có học sinh và chưa bị xóa
        })
            ->whereDoesntHave('rollCalls', function ($subQuery) use ($date) {
                $subQuery->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                    ->whereDate('date', $date); // Lọc lớp chưa có điểm danh trong ngày hôm nay
            })
            ->count();

        // Trả về kết quả
        return [
            'total'                  => $classes->total(),
            'totalClassAttendanced'  => $totalClassAttendanced,
            'totalClassNoAttendance' => $totalClassNoAttendance,
            'data'                   => $classes->map(function ($class) use ($date) {
                // Kiểm tra xem lớp có điểm danh trong ngày không
                $attendanceStatus = $class->rollCalls->isNotEmpty()
                    ? StatusClassAttendance::HAS_CHECKED->value
                    : StatusClassAttendance::NOT_YET_CHECKED->value;

                // Nếu lớp chưa điểm danh trong ngày, gán trạng thái "chưa điểm danh"
                $attendanceBy = $attendanceStatus === StatusClassAttendance::HAS_CHECKED->value
                    ? optional($class->rollCalls)->first()->attendanceBy->fullname
                    : 'Chưa điểm danh';

                // Lấy thông tin giáo viên chủ nhiệm
                $mainTeacher = $class->classSubjectTeacher
                    ->where('access_type', StatusTeacherEnum::MAIN_TEACHER->value)
                    ->first()->user ?? null;

                $studentAttendancedCount = $class->rollCalls
                    ->where('class_id', $class->id)  // Lọc theo lớp
                    ->pluck('student_id')  // Lấy danh sách các student_id
                    ->unique()             // Lọc các student_id duy nhất (không trùng lặp)
                    ->count();  // Đếm số lượng học sinh đã điểm danh


                return [
                    'classId'            => $class->id,
                    'className'          => $class->name ?? null,
                    'grade'              => $class->grade->name ?? null,
                    'fullname'           => $mainTeacher ? $mainTeacher->fullname : 'Chưa có giáo viên chủ nhiệm',
                    'email'              => $mainTeacher ? $mainTeacher->email : null,
                    'status'             => $attendanceStatus,
                    'attendanceBy'       => $attendanceBy,
                    'dateAttendanced'    => strtotime($class->rollCalls->first()->date ?? ''),
                    'attendanceAt'       => $class->rollCalls->first()->time ?? null,
                    'totalStudent'       => $class->classHistory->count(),
                    'studentAttendanced' => $studentAttendancedCount,
                ];
            }),
            'current_page'           => $classes->currentPage(),
            'per_page'               => $classes->perPage(),
        ];
    }


    public function getStudent($class_id, $diemdanh_id, $name = null, $student_code = null)
    {
        $diemdanh = DiemDanh::query()
            ->where('id', $diemdanh_id)
            ->with(['classSubjectTeacher.subject'])
            ->first();
        // Truy vấn học sinh trong lớp
        $studentsQuery = StudentClassHistory::where('class_id', $class_id)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('status', StatusClassStudentEnum::STUDYING->value)
            ->where('status', StatusEnum::ACTIVE->value)
            ->with([
                'student'         => function ($query) {
                    $query->select('id', 'fullname', 'student_code', 'dob');
                },
                'class.rollCalls' => function ($query) {
                    $query->select('note', 'student_id', 'class_id', 'date');
                }
            ]);

        // Nếu có tìm kiếm theo tên, thêm điều kiện vào truy vấn
        if ($name) {
            $studentsQuery->whereHas('student', function ($query) use ($name) {
                $query->where('fullname', 'like', '%'.$name.'%');
            });
        }

        // Nếu có tìm kiếm theo mã học sinh, thêm điều kiện vào truy vấn
        if ($student_code) {
            $studentsQuery->whereHas('student', function ($query) use ($student_code) {
                $query->where('student_code', 'like', '%'.$student_code.'%');
            });
        }

        // Lấy danh sách học sinh
        $studentClassHistoryIds = $studentsQuery->get()->pluck('student_id')->toArray();
        // Lấy tổng số học sinh trong lớp
        $students     = Student::query()->whereIn('id', $studentClassHistoryIds)->where('is_deleted',
            DeleteEnum::NOT_DELETE->value)->get();
        $totalStudent = $students->count();
        // Lấy số học sinh đã điểm danh
        $studentAttendances      = RollCall::where('class_id', $class_id)
            ->where('diemdanh_id', $diemdanh_id)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->with('student')
            ->get();
        $toltalStudentAttendance = $studentAttendances->count();
        // Trả về dữ liệu
        return [
            'totalStudent'            => $totalStudent,            // Tổng số học sinh
            'toltalStudentAttendance' => $toltalStudentAttendance, // Số học sinh đã điểm danh
            "data"                    => $students->map(function ($student) use ($studentAttendances) {
                $studentAttendance = $studentAttendances->where('student_id', $student->id)->first();
                return
                    [
                        'id'           => $student->id,
                        'fullname'     => $student->fullname,
                        'student_code' => $student->student_code,
                        'dob'          => is_null($student->dob) ? 0 : Carbon::parse($student->dob)->timestamp,
                        'status'       => is_null($studentAttendance) ? 0 : $studentAttendance->status,
                        'date'         => is_null($studentAttendance) ? 0 : Carbon::parse($studentAttendance->date)->timestamp,
                        'note'         => is_null($studentAttendance) ? "" : $studentAttendance->note ?? "",
                    ];
            })->toArray(),
            'timetable'               => $diemdanh
        ];
    }


    public function attendanceStudentOfClass($diemdanhId, $classId, $rollCallData = [], $user_id, Carbon $date)
    {
        // Đếm tổng số học sinh trong lớp
        $studentIds     = StudentClassHistory::where('class_id', $classId)
            ->whereNull('end_date')
            ->where('status', StatusClassStudentEnum::STUDYING->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get()->pluck('student_id')->toArray();
        $studentRecords = RollCall::query()
            ->where('class_id', $classId)
            ->where('date', $date->toDateString())
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get()->keyBy('student_id');

        $attendanceLog = AttendanceLog::query()->where('date', $date->toDateString())
            ->where('class_id', $classId)
            ->where('diemdanh_id', $diemdanhId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->first();
        if (is_null($attendanceLog)) {
            AttendanceLog::query()->create(
                [
                    'class_id'    => $classId,
                    'date'        => $date->toDateString(),
                    'user_id'     => $user_id,
                    'diemdanh_id' => $diemdanhId
                ]
            );
        }
        // Lấy tất cả học sinh trong lớp
        $rollCallData              = collect($rollCallData)->whereIn('studentID', $studentIds);
        $dataInsertRollCallHistory = [];
        foreach ($rollCallData as $data) {
            $rollCall = $studentRecords->get($data['studentID']);

            if (!is_null($rollCall)) {
                $dataUpdate = [
                    "student_id"       => $data['studentID'],
                    "note"             => $data['note'],
                    "class_id"         => $classId,
                    "date"             => $date->toDateString(),
                    "time"             => now()->toTimeString(),
                    "status"           => $data['status'],
                    "diemdanh_id"      => $diemdanhId,
                    "modified_user_id" => $user_id,
                ];
                RollCall::query()->where('id', $rollCall->id)->update($dataUpdate);
                $dataInsertRollCallHistory[] = [
                    "student_id"   => $data['studentID'],
                    "note"         => $data['note'],
                    "class_id"     => $classId,
                    "roll_call_id" => $rollCall->id,
                    "date"         => $date->toDateString(),
                    "time"         => now()->toTimeString(),
                    "status"       => $data['status'],
                    "user_id"      => $user_id,
                    "created_at"   => now(),
                    "updated_at"   => now(),
                ];
            } else {
                $dataInsert = [
                    "student_id"      => $data['studentID'],
                    "note"            => $data['note'],
                    "class_id"        => $classId,
                    "date"            => $date->toDateString(),
                    "time"            => now()->toTimeString(),
                    "status"          => $data['status'],
                    "diemdanh_id"     => $diemdanhId,
                    "created_user_id" => $user_id,
                ];
                $rollCall   = RollCall::query()->create($dataInsert);
                CreateNotification::dispatch($rollCall);

                $dataInsertRollCallHistory[] = [
                    "student_id"   => $data['studentID'],
                    "note"         => $data['note'],
                    "class_id"     => $classId,
                    "roll_call_id" => $rollCall->id,
                    "date"         => $date->toDateString(),
                    "time"         => now()->toTimeString(),
                    "status"       => $data['status'],
                    "user_id"      => $user_id,
                    "created_at"   => now(),
                    "updated_at"   => now(),
                ];
            }
        }
        RollCallHistory::query()->insert($dataInsertRollCallHistory);
    }

    public function checkAttendanceLog($id, $diemdanhId)
    {
        return AttendanceLog::query()->where('class_id', $id)
            ->where('diemdanh_id', $diemdanhId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->exists();
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
        if (!is_null($keyWord)) {
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
