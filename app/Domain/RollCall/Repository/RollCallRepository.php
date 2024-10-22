<?php

namespace App\Domain\RollCall\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\statusClassAttendance;
use App\Domain\RollCall\Models\RollCall;
use App\Models\StudentClassHistory;
use Carbon\Carbon;

class RollCallRepository
{
    public function getAllRollCalls($keyword = "", $timestamp = null, $pageIndex = 1, $pageSize = 10)
    {
        $query = RollCall::with(['student', 'class.studentClassHistories', 'class.user']);

        $totalClassAttendanced = RollCall::where('status', StatusClassAttendance::HAS_CHECKED->value)
        ->distinct('class_id')
        ->count('class_id');
    
        $totalClassNoAttendance = RollCall::where('status', StatusClassAttendance::NOT_YET_CHECKED->value)
        ->distinct('class_id')
        ->count('class_id');
        if ($timestamp) {
            $date = Carbon::createFromTimestamp($timestamp);
            $startDate = $date->startOfDay();
            $endDate = $date->endOfDay();

            $query->whereBetween('date', [$startDate, $endDate]);
        }

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->whereHas('class', function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', '%' . $keyword . '%');
                })
                ->orWhereHas('class.grade', function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', '%' . $keyword . '%');
                })
                ->orWhereHas('class.user', function ($q) use ($keyword) {
                    $q->where('fullname', 'LIKE', '%' . $keyword . '%');
                });
            });
        }

        $paginatedResult = $query->paginate($pageSize, ['*'], 'page', $pageIndex);

        
        $mappedData = collect($paginatedResult->items())->map(function ($rollCall) {
            $studentAttendanced = RollCall::where('status', StatusClassAttendance::HAS_CHECKED->value)
                ->where('class_id', $rollCall->class_id)
                ->count('student_id');

            return [
                'classID' => $rollCall->class->id,
                'className' => $rollCall->class->name,
                'grade' => $rollCall->class->grade->name,
                'totalStudent' => $rollCall->class->studentClassHistories()->count(),
                'dateAttendanced' => Carbon::parse($rollCall->date)->translatedFormat('l, d/m/Y'),
                'teacherName' => $rollCall->class->user->first()->fullname ?? 'N/A',
                'teacherEmail' => $rollCall->class->user->first()->email ?? 'N/A',
                'time' => $rollCall->time,
                'attendanceBy' => $rollCall->class->user->first()->fullname ?? 'N/A',
                'attendanceAt' => strtotime($rollCall->time),
                'status' => $rollCall->status,
                'studentAttendanced' => $studentAttendanced,
            ];
        });

        
        return [
            'totalClassAttendanced'=>$totalClassAttendanced,
            'totalClassNoAttendance'=>$totalClassNoAttendance,
            'data' => $mappedData,
            'total' => $paginatedResult->total(),
        ];
    }

    public function getStudentClassDetails($classId)
    {
        // Đếm tổng số học sinh trong lớp
        $totalStudent = StudentClassHistory::where('class_id', $classId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->count();
    
        // Lấy tất cả học sinh trong lớp
        $studentClasses = StudentClassHistory::with(['student', 'class.rollCalls'])
            ->where('class_id', $classId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get();
    
        // Map dữ liệu
        $one =  $studentClasses->map(function ($studentClass) use ($totalStudent) {
            // Lấy ghi chú cho học sinh tương ứng
            $note = $studentClass->class->rollCalls
                ->where('student_id', $studentClass->student_id)
                ->pluck('note')
                ->first();
    
            return [
                'className' => $studentClass->class->name ?? 'N/A',
                'fullname' => $studentClass->student->fullname ?? 'N/A',
                'studentDOB' => $studentClass->student->dob ?? 'N/A',
                
                'note' => $note ?: 'N/A',
            ];
        });

        return [
            'data' => $one,
            'totalStudent' => $totalStudent,
        ];
    }
    




    
}
