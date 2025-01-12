<?php

namespace App\Domain\RollcallStatistics\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusStudentEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\RollCall\Models\RollCall;
use App\Domain\RollCallHistory\Models\RollCallHistory;
use App\Domain\Timetable\Controllers\CategoryTimetableController;
use App\Models\CategoryAttendance;
use App\Models\Classes;
use App\Models\Student;
use App\Models\StudentClassHistory;
use App\Models\TeacherSubjectTimetable;
use App\Models\Timetable;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Pagination\LengthAwarePaginator;

class RollcallStatisticsRepository
{


    public function listClasses($pageSize, $keyWord = null, $status = null, $classId = null)
    {
        $query = Classes::where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->with([
                'grade',
                'classHistory'             => function ($query) {
                    $query->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                        ->where('status', StatusEnum::ACTIVE->value)
                        ->where(function ($q) {
                            $q->whereNull('end_date') // Nếu end_date là null
                            ->orWhere('end_date', '>=', now()); // Nếu end_date là ngày sau ngày hiện tại
                        });
                },
                'classSubjectTeacher.user' => function ($query) {
                    $query->select('id', 'fullname', 'email');
                }
            ]);

        // Nếu có từ khóa tìm kiếm
        if ($keyWord) {
            $query->where(function ($q) use ($keyWord) {
                $q->where('name', 'LIKE', '%'.$keyWord.'%')
                    ->orWhereHas('grade', function ($q) use ($keyWord) {
                        $q->where('name', 'LIKE', '%'.$keyWord.'%');
                    })
                    ->orWhereHas('classSubjectTeacher.user', function ($q) use ($keyWord) {
                        $q->where('fullname', 'LIKE', '%'.$keyWord.'%');
                    });
            });
        }

        // Nếu có status, lọc theo status
        if ($status) {
            $query->whereHas('classHistory', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        // Nếu có classId, lọc theo classId
        if ($classId) {
            $query->where('id', $classId);
        }


        $classes = $query->paginate($pageSize);

        return [
            'total'        => $classes->total(),
            'data'         => $classes->map(function ($class) {
                $mainTeacher = $class->classSubjectTeacher
                    ->where('access_type', StatusTeacherEnum::MAIN_TEACHER->value)
                    ->first()->user ?? null;

                return [
                    'class_id'       => $class->id ?? null,
                    'class_name'     => $class->name ?? null,
                    'grade_name'     => $class->grade->name ?? null,
                    'status_class'   => $class->status ?? null,
                    'teacher_name'   => $mainTeacher ? $mainTeacher->fullname : 'Chưa có giáo viên chủ nhiệm',
                    'total_students' => $class->classHistory->count(),
                ];
            }),
            'current_page' => $classes->currentPage(),
            'per_page'     => $classes->perPage(),
        ];
    }


    public function getClassRollCall($classId, $pageSize, $fromDate, $toDate, $time = 1)
    {
        // Lấy tên lớp
        $className = Classes::where('id', $classId)->value('name');

        // Lấy danh sách học sinh chưa nghỉ học và thuộc lớp
        $students = StudentClassHistory::where('class_id', $classId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>', now());
            })
            ->with([
                'student' => function ($query) {
                    $query->select('id', 'fullname', 'student_code');
                }
            ])
            ->get()
            ->unique('student_id');

        // Danh sách ngày từ startDate -> endDate


        $students = Student::query()
            ->whereIn('id', $students->pluck('student_id')->toArray())
            ->get();
        $data     = $students->map(function ($student) use ($time, $fromDate, $toDate, $classId) {
            $startDate     = clone $fromDate;
            $endDate       = clone $toDate;
            $dataOfStudent = collect();

            $totalPresent  = 0;
            $totalAbsent   = 0;
            $totalLate     = 0;
            $totalLicensed = 0;

            for ($currentDate = $startDate; $currentDate->lte($endDate); $currentDate->addDay()) {
                $date = $currentDate;

                $day                = $currentDate->dayOfWeek;
                $categoryAttendance = CategoryAttendance::query()
                    ->where('from_date', '<=', $date->format('Y-m-d'))
                    ->where('to_date', '>=', $date->format('Y-m-d'))
                    ->first();
                if (is_null($categoryAttendance)) {
                    $dataOfStudent->push([
                        'date' => $date->format('Y-m-d'),
                        'data' => [],
                    ]);
                    continue;
                }
                $timetables = Timetable::query()
                    ->where('day', $day)
                    ->where('time', $time)
                    ->get();
                if (!$timetables->isEmpty()) {
                    $dataTimetableRollCall = $timetables->map(function ($timetable) use (
                        $date,
                        $student,
                        $classId,
                        $categoryAttendance
                    ) {
                        $teacherSubjectTimetable = TeacherSubjectTimetable::query()
                            ->where('timetable_id', $timetable->id)
                            ->where('class_id', $classId)
                            ->where('category_attendance_id', $categoryAttendance->id)
                            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                            ->with('subject')
                            ->first();

                        if (is_null($teacherSubjectTimetable)) {
                            return [
                                'period' => $timetable->period,
                                'subject_name' => "",
                                'user_name' => "",
                                'time' => "",
                                'status' => StatusStudentEnum::HOLIDAY->value
                            ];
                        }
                        $rollCall = RollCall::query()
                            ->where('class_id', $classId)
                            ->where('date', $date->format('Y-m-d'))
                            ->where('student_id', $student->id)
                            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                            ->where('teacher_subject_timetable_id', $teacherSubjectTimetable->id)
                            ->with('createdUser')
                            ->first();
                        return [
                            'period'       => $timetable->period,
                            'subject_name' => is_null($teacherSubjectTimetable->subject) ? $teacherSubjectTimetable->subject->name : "",
                            'user_name'    => is_null($rollCall) ? $rollCall->createdUser->fullname : "",
                            'time'         => is_null($rollCall) ? $rollCall->time : "",
                            'status'       => is_null($rollCall) ? StatusStudentEnum::HOLIDAY->value : $rollCall->status,
                        ];
                    })->toArray();
                } else {
                    $dataTimetableRollCall = [];
                }

                if (!empty($dataTimetableRollCall)) {
                    foreach ($dataTimetableRollCall as $timetableRollCall) {
                        if ($timetableRollCall['status'] == StatusStudentEnum::PRESENT->value) {
                            $totalPresent++;
                        } elseif ($timetableRollCall['status'] == StatusStudentEnum::UN_PRESENT->value) {
                            $totalAbsent++;
                        } elseif ($timetableRollCall['status'] == StatusStudentEnum::UN_PRESENT_PER->value) {
                            $totalLicensed++;
                        } elseif ($timetableRollCall['status'] == StatusStudentEnum::LATE->value) {
                            $totalLate++;
                        }
                    }
                }


                $dataOfStudent->push([
                    'date' => $date->format('d-m-Y'),
                    'data' => $dataTimetableRollCall
                ]);
            }
            return [
                'total_present'  => $totalPresent,
                'total_absent'   => $totalAbsent,
                'total_late'     => $totalLate,
                'total_licensed' => $totalLicensed,
                'student_id'     => $student->id,
                'student_code'   => $student->student_code,
                'student_name'   => $student->fullname,
                'data'           => $dataOfStudent->toArray(),
            ];
        })->toArray();


        // Tính tổng số học sinh
        $totalStudents = $students->count();


        // Trả về kết quả
        return [
            'message'        => 'Lấy lịch sử điểm danh thành công',
            'status'         => 'success',
            'class_id'       => $classId,
            'class_name'     => $className,
            'total_students' => $totalStudents,
            'data'           => $data,

        ];
    }


}
