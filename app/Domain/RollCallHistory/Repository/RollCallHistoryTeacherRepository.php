<?php

namespace App\Domain\RollCallHistory\Repository;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\GenderEnum;
use App\Common\Enums\PaginateEnum;
use App\Common\Enums\StatusClassAttendance;
use App\Common\Enums\StatusClassStudentEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusStudentEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\RollCall\Models\RollCall;
use App\Domain\RollCallHistory\Models\RollCallHistory;
use App\Models\CategoryAttendance;
use App\Models\Classes;
use App\Models\ClassModel;
use App\Models\ClassSubjectTeacher;
use App\Models\Student;
use App\Models\StudentClassHistory;
use App\Models\TeacherSubjectTimetable;
use App\Models\Timetable;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RollCallHistoryTeacherRepository
{


    public function getClassTeacher($user_id): array
    {
        $classSubjectTeachers = ClassSubjectTeacher::query()
            ->where('status', StatusEnum::ACTIVE->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereIn('access_type', [StatusTeacherEnum::MAIN_TEACHER->value, StatusTeacherEnum::TEACHER->value])
            ->whereNull('end_date')
            ->with(
                [
                    'class',
                    'class.schoolYear',
                    'class.grade',
                    'class.academicYear',
                    'class.user'
                ]
            )
            ->where('user_id', $user_id)
            ->get();

        return $classSubjectTeachers->map(function ($classSubjectTeacher) {
            $class = $classSubjectTeacher->class;
            return [
                'classId'   => $classSubjectTeacher->class->id,
                'className' => $classSubjectTeacher->class->name,
                "schoolYear"     => is_null($class->schoolYear->name) ? "" : $class->schoolYear->name,
                "school_year_id" => is_null($class->schoolYear) ? 0 : $class->schoolYear->id,
                "grade_name"          => is_null($class->grade->name) ? "" : $class->grade->name,
                "academic_name"  => is_null($class->academicYear->name) ? "" : $class->academicYear->name,
                "academic_id"    => is_null($class->academicYear) ? 0 : $class->academicYear->id,
                "academic_code"  => is_null($class->academicYear->code) ? "" : $class->academicYear->code,
                "teacher_id"     => is_null($class->user->first()) ? "" : (is_null($class->user->first()->id) ? "" : $class->user->first()->id),
                "teacher_name"   => is_null($class->user->first()) ? "" : (is_null($class->user->first()->fullname) ? "" : $class->user->first()->fullname),
                "teacher_email"  => is_null($class->user->first()) ? "" : (is_null($class->user->first()->email) ? "" : $class->user->first()->email),
                "status_teacher" => $classSubjectTeacher->access_type,
                "status"         => is_null($class->status) ? "1" : $class->status,
            ];
        })->toArray();
    }

    public function getClassRollCallHistories($classId, $pageSize, $keyWord = null, $date = null, $user_id)
    {
        $classSubjectTeachers = ClassSubjectTeacher::query()
            ->where('status', StatusEnum::ACTIVE->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereIn('access_type', [StatusTeacherEnum::MAIN_TEACHER->value, StatusTeacherEnum::TEACHER->value])
            ->whereNull('end_date')
            ->with(
                [
                    'class',
                    'class.schoolYear',
                    'class.grade',
                    'class.academicYear',
                    'class.user'
                ]
            )
            ->where('user_id', $user_id)
            ->get();

        // Tính tổng số học sinh
        $totalStudents = StudentClassHistory::where('class_id', $classId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereNull('end_date')
            ->count();

        // Lấy tên lớp
        $class = Classes::find($classId);
        $className = $class ? $class->name : 'Unknown';
        $classID = $class ? $class->id : 'Unknown';
        // Lấy danh sách lịch sử điểm danh
        $rollCallHistoriesQuery = RollCallHistory::where('class_id', $classId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->with([
                'user' => function ($query) {
                    $query->select('id', 'fullname', 'email');
                },
                'rollCall.teacherSubjectTimetable.timetable',
                'rollCall.teacherSubjectTimetable.classSubjectTeacher.subject',
                'rollCall.teacherSubjectTimetable.classSubjectTeacher.user',
            ])
            ->orderBy('date', 'desc');

        // Tìm kiếm theo từ khóa
        if ($keyWord) {
            $rollCallHistoriesQuery->whereHas('user', function ($query) use ($keyWord) {
                $query->where('fullname', 'like', '%' . $keyWord . '%')
                    ->orWhere('email', 'like', '%' . $keyWord . '%');
            });
        }

        // Lọc theo ngày
        // Lọc theo ngày, mặc định là hôm nay
        if ($date) {
            $rollCallHistoriesQuery->whereDate('date', $date);
        } else {
            $rollCallHistoriesQuery->whereDate('date', Carbon::today()->toDateString());
        }


        $rollCallHistories = $rollCallHistoriesQuery->get()->groupBy(function ($history) {
            return Carbon::parse($history->date)->toDateString(); // Nhóm theo ngày (YYYY-MM-DD)
        });

        // Tạo mảng kết quả
        $data = $rollCallHistories->map(function ($histories, $date) use (&$totals) {
            $histories = $histories->sortBy('period');

            $morningTimetable = [];
            $afternoonTimetable = [];

            $morningTimetable = collect($morningTimetable)
                ->unique(function ($item) {
                    return $item['period'] . $item['from_time'] . $item['to_time']; // Kết hợp period, from_time và to_time
                })
                ->values(); // Loại bỏ các chỉ mục không liên tiếp (0, 45, ...)

            $afternoonTimetable = collect($afternoonTimetable)
                ->unique(function ($item) {
                    return $item['period'] . $item['from_time'] . $item['to_time']; // Kết hợp period, from_time và to_time
                })
                ->values(); // Loại bỏ các chỉ mục không liên tiếp (0, 45, ...)



            foreach ($histories as $history) {
                $rollCall = $history->rollCall;
                $teacherSubjectTimetable = $rollCall->teacherSubjectTimetable;

                // Kiểm tra nếu teacherSubjectTimetable và timetable có tồn tại
                if (!$teacherSubjectTimetable || !$teacherSubjectTimetable->timetable) {
                    continue; // Nếu không có timetable thì bỏ qua bản ghi này
                }

                $timetable = $teacherSubjectTimetable->timetable;
                $createdUser = $rollCall->createdUser; // Lấy thông tin giáo viên từ created_user_id
                $teacherSubject = $rollCall->teacher_subject_timetable_id;
                $fromTime = Carbon::parse($timetable->from_time);
                $toTime = Carbon::parse($timetable->to_time);

                // Xác định buổi sáng hay chiều (giả sử buổi sáng từ 7:00 AM - 12:00 PM, chiều từ 12:00 PM - 6:00 PM)
                $formattedTimetable = $this->formatTimetableData($totals, $history, $teacherSubjectTimetable, $timetable, $createdUser, $teacherSubject);

                // Lọc các tiết trùng lặp dựa trên 'period' hoặc 'from_time', 'to_time'
                if ($fromTime->hour >= 7 && $fromTime->hour < 12) {
                    $morningTimetable[] = $formattedTimetable;
                } elseif ($fromTime->hour >= 2 && $fromTime->hour < 6) {
                    $afternoonTimetable[] = $formattedTimetable;
                }
            }

            // Lọc bỏ các tiết trùng lặp trong buổi sáng và chiều
            $morningTimetable = $this->removeDuplicatePeriods($morningTimetable);
            $afternoonTimetable = $this->removeDuplicatePeriods($afternoonTimetable);

            return [
                'date' => Carbon::parse($date)->timestamp,
                'morning_timetable' => $morningTimetable,
                'afternoon_timetable' => $afternoonTimetable,
            ];

            // Hàm lọc các tiết trùng lặp


        })->values();

        // Phân trang thủ công
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

        return [
            'message' => 'Lấy lịch sử điểm danh thành công',
            'status' => 'success',
            'total_students' => $totalStudents,
            'class_name' => $className,
            'class_id' => $classID,
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'pageIndex' => $paginator->currentPage(),
            'pageSize' => $paginator->perPage(),
        ];
    }

    private function formatTimetableData(&$totals, $history, $teacherSubjectTimetable, $timetable, $createdUser, $teacherSubject)
    {

        return [
            'period' => $timetable->period ?? null,
            'from_time' => $timetable->from_time ?? null,
            'to_time' => $timetable->to_time ?? null,
            'day' => $timetable->day ?? null,
            'subject' => $history->rollCall->teacherSubjectTimetable->classSubjectTeacher->subject->name ?? null,
            'teacher_name' => $createdUser->fullname ?? null,
            'teacher_email' => $createdUser->email ?? null,
            'teacherSubject' => $teacherSubject
        ];
    }

    private function removeDuplicatePeriods($timetable)
    {
        return $timetable->unique(function ($item) {
            return $item['period'] . $item['from_time'] . $item['to_time'];
        })->values();
    }

    public function getClassRollCallHistoryDetailsByDate($class_id, $teacher_subject_timetable_id)
    {
        // Nếu không có tham số ngày, gán ngày hiện tại

        // Lấy thông tin thời khóa biểu của giảng viên
        $teacherSubjectTimetable = TeacherSubjectTimetable::query()
            ->where('id', $teacher_subject_timetable_id)
            ->with(['classSubjectTeacher.subject', 'timetable'])
            ->first();

        $timetable = $teacherSubjectTimetable->timetable ?? null;

        // Truy vấn danh sách học sinh theo lớp học, trạng thái và các điều kiện
        $studentsQuery = StudentClassHistory::where('class_id', $class_id)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereNull('end_date')
            ->where('status', StatusClassStudentEnum::STUDYING->value)
            ->where('status', StatusEnum::ACTIVE->value)
            ->with([
                'student' => function ($query) {
                    $query->select('id', 'fullname', 'student_code', 'dob');
                },
                'class.rollCalls' => function ($query) {
                    $query->select('note', 'student_id', 'class_id', 'date');
                }
            ]);

        // Lấy danh sách học sinh
        $studentClassHistoryIds = $studentsQuery->get()->pluck('student_id')->toArray();
        $students = Student::query()->whereIn('id', $studentClassHistoryIds)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get();

        // Tính tổng số học sinh
        $totalStudent = $students->count();

        // Lấy danh sách điểm danh của học sinh trong lớp và ngày
        $studentAttendances = RollCall::where('class_id', $class_id)
            ->where('teacher_subject_timetable_id', $teacher_subject_timetable_id)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->with(['student', 'rollCallHistories', 'class']) // Load rollCallHistories for each RollCall
            ->get();

        // Tính số học sinh đã điểm danh
        $toltalStudentAttendance = $studentAttendances->where('status', StatusStudentEnum::PRESENT->value)->count();
        $toltalStudentUnPresent = $studentAttendances->where('status', StatusStudentEnum::UN_PRESENT->value)->count();
        $toltalStudentUnPresentPer = $studentAttendances->where('status', StatusStudentEnum::UN_PRESENT_PER->value)->count();
        $toltalStudentLate = $studentAttendances->where('status', StatusStudentEnum::LATE->value)->count();
        $className = $studentAttendances->first()->class;
        // Nếu không có điểm danh vào ngày hiện tại, tìm điểm danh của kỳ trước
        $period = optional($timetable)->period;

        if ($period && (int)$period > 1) {
            $diemdanhtruoc = Timetable::query()
                ->where('period', ((int)$period) - 1)
                ->where('day', $timetable->day)
                ->where('time', $timetable->time)
                ->first();
            // Xử lý nếu có điểm danh kỳ trước
        }

        // Trả về kết quả
        return [
            'totalStudent' => $totalStudent, // Tổng số học sinh
            'toltalStudentAttendance' => $toltalStudentAttendance, // Số học sinh đã điểm danh
            'toltalStudentUnPresent' => $toltalStudentUnPresent,
            'toltalStudentUnPresentPer' => $toltalStudentUnPresentPer,
            'toltalStudentLate' => $toltalStudentLate,
            'className' => $className->name ?? null,
            "data" => $students->map(function ($student) use ($studentAttendances) {
                // Lấy trạng thái và ghi chú điểm danh từ rollCallHistories
                $status = null;
                $note = null;

                // Loop through all student attendances and find the matching rollCallHistory
                $studentAttendance = $studentAttendances->firstWhere('student_id', $student->id);

                if ($studentAttendance && $studentAttendance->rollCallHistories) {
                    // Find the rollCallHistory for this student
                    $rollCallHistory = $studentAttendance->rollCallHistories
                        ->where('student_id', $student->id)
                        ->sortByDesc('created_at') // Sắp xếp giảm dần theo thời gian
                        ->first();

                    if ($rollCallHistory) {
                        $status = $rollCallHistory->status ?? null;
                        $note = $rollCallHistory->note ?? null;
                    }
                }

                return [
                    'id' => $student->id,
                    'fullname' => $student->fullname,
                    'student_code' => $student->student_code,
                    'dob' => is_null($student->dob) ? 0 : Carbon::parse($student->dob)->timestamp,
                    'gender' => $student->gender,
                    'status' => $status ?? null,
                    'note' => $note ?? '',
                ];
            })->toArray(),
        ];
    }
}
