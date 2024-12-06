<?php

namespace App\Domain\RollCall\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\statusClassAttendance;
use App\Common\Enums\StatusClassStudentEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusStudentEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\RollCall\Models\RollCall;
use App\Jobs\CreateNotification;
use App\jobs\NotificationJob;
use App\Models\Classes;
use App\Models\Student;
use App\Models\StudentClassHistory;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RollCallRepository
{
    public function getClass($pageIndex = 1, $pageSize = 10, $keyWord = null, $date = null)
    {
        $query = Classes::where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->with([
                'grade',
                'rollCalls' => function ($query) {
                    $query->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                        ->where('status');
                },
                'classHistory' => function ($query) {
                    $query->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                        ->where('status', StatusEnum::ACTIVE->value);
                },
                'classSubjectTeacher.user' => function ($query) {
                    $query->select('id', 'fullname', 'email');
                }
            ]);


        if ($keyWord) {
            $query->where(function ($q) use ($keyWord) {
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

                    $status = $class->rollCalls->first()->status ?? null;

                return [
                    'class_id' => $class->id,
                    'className' => $class->name ?? null,
                    'grade' => $class->grade->name ?? null,
                    'fullname' => $mainTeacher ? $mainTeacher->fullname : 'Chưa có giáo viên chủ nhiệm',
                    'email' => $mainTeacher ? $mainTeacher->email : null,
                    'status' => $status,
                    'total_students' => $class->classHistory->count(),
                ];
            }),
            'current_page' => $classes->currentPage(),
            'per_page' => $classes->perPage(),
        ];
    }



    public function getStudentClassDetails($classId, $rollCallData = [], $user_id)
    {
        // Đếm tổng số học sinh trong lớp
        $totalStudent = StudentClassHistory::where('class_id', $classId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->count();

        $totalStudentNotAttendaced = RollCall::where('status', StatusStudentEnum::UN_PRESENT)
            ->where('class_id', $classId)
            ->count();

        $totalStudentAttendaced = RollCall::where('status', StatusStudentEnum::PRESENT)
            ->where('class_id', $classId)
            ->count();

        // Lấy tất cả học sinh trong lớp
        $studentClasses = StudentClassHistory::with(['student'])
            ->where('class_id', $classId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get();


        $insertedRollCalls = [];


        if (!empty($rollCallData)) {
            foreach ($rollCallData as $data) {
                $studentID = $data['studentID'];
                $status = $data['status'];
                $note = $data['note'] ?? null;


                $studentClass = $studentClasses->firstWhere('student_id', $studentID);

                if (!$studentClass) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Niên khóa không tồn tại cho học sinh với ID: ' . $studentID,
                        'status' => 400
                    ], 400);
                }

                if ($studentClass) {

                    $rollCall = RollCall::create([
                        'student_id' => $studentClass->student_id,
                        'class_id' => $classId,
                        'status' => $status,
                        'date' => now()->format('Y-m-d'),
                        'time' => now()->format('H:i:s'),
                        'note' => $note,
                        'created_user_id' => $user_id,
                    ]);
                    CreateNotification::dispatch($rollCall);


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
            'insert_roll_call' => $insertedRollCalls,
        ];
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
                    'time' => now(),
                    'note' => $student['note'],
                    'status' => $student['status'],
                    'modified_user_id' => $user_id,
                ]);
                CreateNotification::dispatch($rollCall);
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
            $query->where('fullname', 'like', '%' . $keyWord . '%');
        }
        return [$query->get(), count($studentIds)];
    }

    public function getRollCall(int $classId, Collection $students, Carbon $date): array
    {
        $studentIds = $students->pluck('id')->toArray();
        $rollCalls = RollCall::query()
            ->where('class_id', $classId)
            ->whereIn('student_id', $studentIds)
            ->where('date', $date->toDateString())
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get();
        return $students->map(function ($student) use ($rollCalls) {
            $status = StatusStudentEnum::PRESENT->value;
            $note = "";
            $rollCall = $rollCalls->where('student_id', $student->id)->first();
            if (!is_null($rollCall)) {
                $status = $rollCall->status;
                $note = $rollCall->note;
            }
            return [
                'fullname' => $student->fullname ?? "",
                'code' => $student->student_code ?? "",
                'dob' => $student->dob ?? "",
                'status' => $status,
                'note' => $note,
            ];
        })->toArray();
    }
}
