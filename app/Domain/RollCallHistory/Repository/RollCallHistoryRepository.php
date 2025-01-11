<?php

namespace App\Domain\RollCallHistory\Repository;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\GenderEnum;
use App\Common\Enums\PaginateEnum;
use App\Common\Enums\StatusClassStudentEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusStudentEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\RollCallHistory\Models\RollCallHistory;
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

class RollCallHistoryRepository
{


    public function getClassesWithRollCallHistories($pageSize, $keyWord = null)
    {
        $query = Classes::where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->with(['grade', 'classHistory' => function ($query) {
                $query->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                    ->where('status', StatusEnum::ACTIVE->value)
                    ->whereNull('end_date');
            }, 'classSubjectTeacher.user' => function ($query) {
                $query->select('id', 'fullname', 'email');
            }]);

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

                return [
                    'class_id' => $class->id,
                    'class_name' => $class->name ?? null,
                    'grade_name' => $class->grade->name ?? null,
                    'teacher_name' => $mainTeacher ? $mainTeacher->fullname : 'Chưa có giáo viên chủ nhiệm',
                    'teacher_email' => $mainTeacher ? $mainTeacher->email : null,
                    'total_students' => $class->classHistory->count(),
                ];
            }),
            'current_page' => $classes->currentPage(),
            'per_page' => $classes->perPage(),
        ];
    }

    public function getClassRollCallHistories($classId, $pageSize, $keyWord = null, $date = null)
    {
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

                $fromTime = Carbon::parse($timetable->from_time);
                $toTime = Carbon::parse($timetable->to_time);

                // Xác định buổi sáng hay chiều (giả sử buổi sáng từ 7:00 AM - 12:00 PM, chiều từ 12:00 PM - 6:00 PM)
                $formattedTimetable = $this->formatTimetableData($totals, $history, $teacherSubjectTimetable, $timetable, $createdUser);

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

    private function formatTimetableData(&$totals, $history, $teacherSubjectTimetable, $timetable, $createdUser)
    {

        return [
            'id' => $timetable->id,
            'period' => $timetable->period ?? 'unknow',
            'from_time' => $timetable->from_time ?? null,
            'to_time' => $timetable->to_time ?? null,
            'day' => $timetable->day ?? null,
            'subject' => $history->rollCall->teacherSubjectTimetable->classSubjectTeacher->subject->name ?? 'unknow',
            'teacher_name' => $createdUser->fullname ?? 'unknow',
            'teacher_email' => $createdUser->email ?? 'unknow',
        ];
    }

    private function removeDuplicatePeriods($timetable)
    {
        return $timetable->unique(function ($item) {
            return $item['period'] . $item['from_time'] . $item['to_time'];
        })->values();
    }



    public function getClassRollCallHistoryDetailsByDate($classId, $timetableId, $date)
    {
        // Lấy tên lớp và thời khóa biểu
        $class = Classes::find($classId);
        $timeTable = ClassSubjectTeacher::find($timetableId);

        // Lấy danh sách học sinh trong lớp (đã được đăng ký) theo classId
        $students = StudentClassHistory::where('class_id', $classId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereNull('end_date') // Chỉ lấy học sinh chưa hết hạn
            ->get();

        // Lấy danh sách lịch sử điểm danh với điều kiện theo classId, timetableId và ngày
        $rollCallHistoriesQuery = RollCallHistory::where('class_id', $classId)
            ->whereHas('rollCall', function ($query) use ($timetableId) {
                $query->where('teacher_subject_timetable_id', $timetableId); // Lọc theo timetableId
            })
            ->whereDate('date', $date)  // Lọc theo ngày
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->with([
                'user' => function ($query) {
                    $query->select('id', 'fullname', 'email');
                },
                'rollCall.teacherSubjectTimetable.timetable',
                'rollCall.teacherSubjectTimetable.classSubjectTeacher.subject',
                'rollCall.teacherSubjectTimetable.classSubjectTeacher.user',
                'rollCall',
                'student'  // Lấy thông tin học sinh
            ])
            ->orderBy('date', 'desc');

        // Thực hiện truy vấn
        $rollCallHistories = $rollCallHistoriesQuery->get();

        // Nhóm theo tiết học (period)
        $groupedByPeriod = $rollCallHistories->groupBy(function ($history) {
            return $history->rollCall->teacherSubjectTimetable->period; // Nhóm theo period của tiết học
        });

        // Duyệt qua các kết quả và lấy thông tin học sinh, ngày sinh và trạng thái điểm danh
        $result = $groupedByPeriod->map(function ($histories, $period) use ($students) {
            // Mảng chứa tất cả học sinh theo tiết
            $studentStatuses = $students->map(function ($student) use ($histories) {
                // Kiểm tra nếu học sinh có điểm danh trong tiết này
                $history = $histories->firstWhere('student_id', $student->id);
                return [
                    'student_name' => $student->fullname,
                    'dob' => $student->dob,
                    'status' => $history ? $history->status : 'Chưa điểm danh', // Trạng thái điểm danh (nếu có, nếu không là 'Chưa điểm danh')
                    'is_in_class' => true, // Học sinh có trong lớp
                ];
            });

            return [
                'period' => $period,
                'students' => $studentStatuses,
            ];
        });

        return $result;
    }
}
