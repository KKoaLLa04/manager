<?php

namespace App\Domain\RollCallHistory\Repository;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\GenderEnum;
use App\Common\Enums\PaginateEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusStudentEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\RollCallHistory\Models\RollCallHistory;
use App\Models\Classes;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\StudentClassHistory;
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
                } elseif ($fromTime->hour >= 12 && $fromTime->hour < 18) {
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



    public function getClassRollCallHistoryDetailsByDate($classId, $date = null)
    {
        // Nếu không có ngày được truyền vào, mặc định là ngày hôm nay
        if (!$date) {
            $date = Carbon::today()->toDateString();
        }

        $totalStudents = StudentClassHistory::where('class_id', $classId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where(function ($query) use ($date) {
                $query->whereNull('end_date')                // Học sinh chưa rời lớp
                    ->orWhereDate('end_date', '>', $date);   // Hoặc còn học trong lớp sau ngày `$date`
            })
            ->count();

        $class = Classes::find($classId);
        $className = $class ? $class->name : 'Unknown';

        // Lấy danh sách lịch sử điểm danh theo class_id và ngày cụ thể
        $rollCallHistories = RollCallHistory::where('class_id', $classId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereDate('date', $date)
            ->with(['student' => function ($query) {
                // Lấy thông tin chi tiết của học sinh
                $query->select('id', 'student_code', 'fullname', 'dob', 'gender');
            }])
            ->orderBy('time', 'asc')
            ->get();

        // Kiểm tra nếu không có dữ liệu
        if ($rollCallHistories->isEmpty()) {
            return [
                'message' => 'Không có lịch sử điểm danh cho ngày này',
                'status' => 'error',
            ];
        }

        // Chuẩn bị các biến đếm số lượng học sinh
        $totalRollCalledStudents = 0; // Số học sinh đã điểm danh (có mặt)
        $totalStudentNotAttendance = 0; // Số học sinh vắng mặt không phép
        $totalStudentPolicy = 0; // Số học sinh đi muộn có phép

        // Chuẩn bị mảng dữ liệu trả về
        $data = $rollCallHistories->map(function ($history) use (&$totalRollCalledStudents, &$totalStudentNotAttendance, &$totalStudentPolicy) {
            $gender = $history->student->gender;

            // Kiểm tra trạng thái điểm danh và cập nhật các biến đếm
            switch ($history->status) {
                case StatusStudentEnum::PRESENT->value:
                    $totalRollCalledStudents++; // Học sinh có mặt
                    break;
                case StatusStudentEnum::UN_PRESENT->value:
                    $totalStudentNotAttendance++; // Học sinh vắng mặt không phép
                    break;
                case StatusStudentEnum::LATE->value:
                case StatusStudentEnum::UN_PRESENT_PER->value:
                    $totalStudentPolicy++; // Học sinh đến muộn có phép
                    break;
            }

            return [
                'fullname' => $history->student ? $history->student->fullname : 'Unknown',
                'student_code' => $history->student ? $history->student->student_code : 'Unknown',
                'dob' => $history->student ? strtotime($history->student->dob) : null, // Ngày sinh
                'gender' => $gender,
                'note' => $history->note ?? 'Không có ghi chú', // Ghi chú nếu có
                'status' => StatusStudentEnum::from($history->status)->value, // Trạng thái điểm danh (PRESENT, UN_PRESENT, etc.)
            ];
        });

        return [
            'message' => 'Lấy chi tiết lịch sử điểm danh thành công',
            'status' => 'success',
            'date' => Carbon::parse($date)->translatedFormat('l, d/m/Y'), // Hiển thị ngày đã chọn
            'class_id' => $classId,
            'class_name' => $className,
            'total_students' => $totalStudents, // Tổng số học sinh
            'total_Students_Attended' => $totalRollCalledStudents, // Tổng số học sinh đã điểm danh
            'total_Student_NotAttendance' => $totalStudentNotAttendance, // Số học sinh vắng mặt không phép
            'total_Student_Policy' => $totalStudentPolicy, // Số học sinh đến muộn có phép
            'data' => $data, // Dữ liệu chi tiết của các học sinh đã điểm danh
        ];
    }
}
