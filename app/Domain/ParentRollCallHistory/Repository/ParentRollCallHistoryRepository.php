<?php

namespace App\Domain\ParentRollCallHistory\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusStudentEnum;
use App\Domain\ParentRollCallHistory\Models\ParentRollCallHistory;
use App\Domain\RollCallHistory\Models\RollCallHistory;
use App\Models\Student;
use App\Models\StudentClassHistory;
use App\Models\UserStudent;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class ParentRollCallHistoryRepository
{
    public function getParentStudentRollCallHistories($userId, $pageSize, $keyWord = null, $date = null, $studentId = null)
    {
        // Lấy danh sách student_id của các con của phụ huynh
        $studentIds = UserStudent::where('user_id', $userId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->pluck('student_id');

        if ($studentIds->isEmpty()) {
            return $this->responseError('Không có học sinh nào', $pageSize);
        }

        if ($studentId && !$studentIds->contains($studentId)) {
            return $this->responseError('Học sinh không tồn tại hoặc không thuộc quyền quản lý của phụ huynh này', $pageSize);
        }

        $studentId = $studentId ?? $studentIds->first();
        $date = $date ? Carbon::parse($date) : Carbon::now();

        $rollCallHistoriesQuery = ParentRollCallHistory::where('student_id', $studentId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->with([
                'rollCall.teacherSubjectTimetable.timetable',
                'rollCall.teacherSubjectTimetable.classSubjectTeacher.subject',
                'rollCall.teacherSubjectTimetable.classSubjectTeacher.user',
                'rollCall.createdUser' // Thêm quan hệ đến giáo viên qua created_user_id
            ])
            ->orderBy('date', 'desc'); // Lấy bản mới nhất trước


        if ($keyWord) {
            $rollCallHistoriesQuery->whereHas('rollCall.teacherSubjectTimetable.classSubjectTeacher.user', function ($query) use ($keyWord) {
                $query->where('fullname', 'like', "%$keyWord%")
                    ->orWhere('email', 'like', "%$keyWord%");
            });
        }

        if ($date) {
            $rollCallHistoriesQuery->whereDate('date', $date);
        }

        $rollCallHistories = $rollCallHistoriesQuery->get()->groupBy(fn($history) => Carbon::parse($history->date)->toDateString());

        if ($rollCallHistories->isEmpty()) {
            return $this->responseError('Con của phụ huynh chưa có lịch sử điểm danh.', $pageSize);
        }

        $totals = ['present' => 0, 'absent' => 0, 'late' => 0];

        $data = $rollCallHistories->map(function ($histories, $date) use (&$totals) {
            $histories = $histories->sortBy('period');

            $morningTimetable = [];
            $afternoonTimetable = [];

            $morningTimetable = collect($morningTimetable)->unique(function ($item) {
                return $item['period'] . $item['from_time'] . $item['to_time']; // Kết hợp period, from_time và to_time
            });

            $afternoonTimetable = collect($afternoonTimetable)->unique(function ($item) {
                return $item['period'] . $item['from_time'] . $item['to_time']; // Kết hợp period, from_time và to_time
            });

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


        })->values();

        $studentName = $rollCallHistories->first()->first()->student->fullname ?? 'Chưa có học sinh';
        $className = $rollCallHistories->first()->first()->classes->name ?? 'Chưa có lớp';
        $code = $rollCallHistories->first()->first()->student->student_code?? null;
        $dob = $rollCallHistories->first()->first()->student->dob?? null;

        return $this->paginateResponse($data, $totals, $pageSize, $studentName, $className, $code, $dob);
    }

    private function removeDuplicatePeriods($timetable) {
        return $timetable->unique(function ($item) {
            return $item['period'] . $item['from_time'] . $item['to_time']; // Kết hợp period, from_time và to_time
        });
    }

    private function formatTimetableData(&$totals, $history, $teacherSubjectTimetable, $timetable, $createdUser)
    {
        $this->incrementTotals($totals, $history->status);

        return [
            'period' => $timetable->period ?? 'unknow',
            'from_time' => $timetable->from_time ?? null,
            'to_time' => $timetable->to_time ?? null,
            'day' => $timetable->day ?? null,
            'subject' => $history->rollCall->teacherSubjectTimetable->classSubjectTeacher->subject->name ?? 'unknow',
            'status' => $history->status,
            'note' => $history->note ?? 'unknow',
            'teacher_name' => $createdUser->fullname ?? 'unknow',
            'teacher_phone' => $createdUser->phone ?? 'unknow',
        ];
    }


    private function incrementTotals(&$totals, $status)
    {
        if ($status === StatusStudentEnum::PRESENT->value) $totals['present']++;
        elseif ($status === StatusStudentEnum::UN_PRESENT->value) $totals['absent']++;
        elseif ($status === StatusStudentEnum::LATE->value) $totals['late']++;
    }

    private function paginateResponse($data, $totals, $pageSize, $studentName, $className, $code, $dob)
    {
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
            'student_name' => $studentName,
            'class_name' => $className,
            'code' => $code,
            'dob' => $dob ? Carbon::parse($dob)->format('d/m/Y') : null,
            'total_present' => $totals['present'],
            'total_absent' => $totals['absent'],
            'total_late' => $totals['late'],
            'data' => $paginator->items(),
            'total' => $paginator->total(),
            'pageIndex' => $paginator->currentPage(),
            'pageSize' => $paginator->perPage(),
        ];
    }

    private function responseError($message, $pageSize)
    {
        return [
            'message' => $message,
            'status' => 'error',
            'data' => [],
            'total' => 0,
            'pageIndex' => 1,
            'pageSize' => $pageSize,
        ];
    }






}















