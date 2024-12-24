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

        $rollCallHistoriesQuery = ParentRollCallHistory::where('student_id', $studentId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->with([
                'rollCall.teacherSubjectTimetable.timetable',
                'rollCall.teacherSubjectTimetable.classSubjectTeacher.subject',
                'rollCall.teacherSubjectTimetable.classSubjectTeacher.user',
            ])
            ->orderBy('date', 'desc');

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

        $data = $rollCallHistories->map(function ($histories) use (&$totals) {
            $firstHistory = $histories->first();
            $rollCall = $firstHistory->rollCall;
            $teacherSubjectTimetable = $rollCall->teacherSubjectTimetable;
            $timetable = $teacherSubjectTimetable->timetable;

            $this->incrementTotals($totals, $firstHistory->status);

            return [
                'note' => $firstHistory->note,
                'date' => Carbon::parse($firstHistory->date)->timestamp,
                'status' => $firstHistory->status,
                'period' => $timetable->period ?? 'N/A',
                'day' => $timetable->day ?? 'N/A',
                'from_time' => $timetable->from_time ?? 'N/A',
                'to_time' => $timetable->to_time ?? 'N/A',
                'subject' => $teacherSubjectTimetable->subject->name ?? 'N/A',
                'teacher' => $teacherSubjectTimetable->user->fullname ?? 'N/A',
                'email' => $teacherSubjectTimetable->user->email ?? 'N/A',
                'phone' => $teacherSubjectTimetable->user->phone ?? 'N/A',

            ];
        })->values();

        $studentName = $rollCallHistories->first()->first()->student->fullname ?? 'Chưa có học sinh';
        $className = $rollCallHistories->first()->first()->classes->name ?? 'Chưa có lớp';

        return $this->paginateResponse($data, $totals, $pageSize, $studentName, $className);
    }

    private function incrementTotals(&$totals, $status)
    {
        if ($status === StatusStudentEnum::PRESENT->value) $totals['present']++;
        elseif ($status === StatusStudentEnum::UN_PRESENT->value) $totals['absent']++;
        elseif ($status === StatusStudentEnum::LATE->value) $totals['late']++;
    }

    private function paginateResponse($data, $totals, $pageSize, $studentName, $className)
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















