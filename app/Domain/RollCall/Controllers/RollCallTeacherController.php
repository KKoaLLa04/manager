<?php

namespace App\Domain\RollCall\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\GenderEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusStudentEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\Class\Repository\ClassRepository;
use App\Domain\RollCall\Models\RollCall;
use App\Domain\RollCall\Repository\RollCallRepository;
use App\Domain\RollCall\Repository\RollCallTeacherRepository;
use App\Domain\RollCall\Requests\RollCallRequest;
use App\Http\Controllers\BaseController;
use App\Models\Classes;
use App\Models\ClassSubjectTeacher;
use App\Models\DiemDanh;
use App\Models\StudentClassHistory;
use App\Models\Timetable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class RollCallTeacherController extends BaseController
{

    protected $rollCallRepository;

    public function __construct(
        RollCallTeacherRepository $rollCallRepository,
        protected ClassRepository $classRepository,
    ) {
        $this->rollCallRepository = $rollCallRepository;
    }

    public function index(Request $request, GetUserRepository $getUserRepository)
    {
        $user_id       = Auth::user()->id;
        $type          = AccessTypeEnum::TEACHER->value;
        $classId       = $request->classId;
        $statusTeacher = $request->status_teacher;
        $date          = isset($request->date) ? Carbon::parse($request->date) : Carbon::now();
        $dayQuery      = $date->dayOfWeek;

        $showUser = $getUserRepository->getUser($user_id, $type);
        if (!$showUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }


        $class = $this->rollCallRepository->getClassById($classId);
        if (is_null($class)) {
            return $this->responseSuccess([]);
        }
        $timetables                  = Timetable::query()->where('day', $dayQuery)->get();
        $timetableIds                = $timetables->pluck('id')->toArray();
        $getClassSubjectTeachers     = $this->rollCallRepository->getClassSubjectTeacher($classId, $user_id,
            $statusTeacher);
        $classSubjectTeacherIds      = $getClassSubjectTeachers->pluck('id')->toArray();
        $getTeacherSubjectTimetables = $this->rollCallRepository->getTeacherSubjectTimetable($classSubjectTeacherIds,
            $timetableIds);
        $studentIds                  = $this->rollCallRepository->getStudentInClass($classId);
        $data                        = $getTeacherSubjectTimetables->map(function ($teacherSubjectTimetable) use (
            $timetables,
            $getClassSubjectTeachers,
            $studentIds,
            $date
        ) {
            $rollcalls              = $teacherSubjectTimetable->rollcalls;
            $totalChecked           = $rollcalls->whereIn('student_id', $studentIds)->count();
            $attendanceLogs         = $this->rollCallRepository->getAttendanceLog($teacherSubjectTimetable->id,
                $teacherSubjectTimetable->class_id, $date);
            $getClassSubjectTeacher = $getClassSubjectTeachers->where('id',
                $teacherSubjectTimetable->class_subject_teacher_id)->first();
            $timetable              = $timetables->where('id', $teacherSubjectTimetable->timetable_id)->first();
            $from_time              = Carbon::parse($timetable->from_time);
            $to_time                = Carbon::parse($timetable->to_time);
            $current_time           = Carbon::now();
            $canEdit                = $current_time->between($from_time, $to_time);
            return [
                'teacher_subject_timetable_id' => $teacherSubjectTimetable->id,
                'timetable_id'                 => $timetable->id,
                'timetable_time'               => $timetable->time,
                'timetable_period'             => $timetable->period,
                'timetable_from_time'          => $from_time->translatedFormat('H:i'),
                'timetable_to_time'            => $to_time->translatedFormat('H:i'),
                'teacher_id'                   => $getClassSubjectTeacher->teacher->id,
                'teacher_name'                 => $getClassSubjectTeacher->teacher->fullname,
                'teacher_email'                => $getClassSubjectTeacher->teacher->email,
                'subject_id'                   => $getClassSubjectTeacher->subject->id,
                'subject_name'                 => $getClassSubjectTeacher->subject->name,
                'totalChecked'                 => $totalChecked,
                'totalStudent'                 => count($studentIds),
                'attendance_checked'           => $attendanceLogs->isEmpty() ? 0 : 1,
                'attendance_histories'         => $attendanceLogs->map(function ($attendanceLog) {
                    return [
                        'user_name' => $attendanceLog->user->fullname,
                        'type'      => $attendanceLog->type,
                        'time'      => Carbon::parse($attendanceLog->time)->format('d-m-Y H:i:s'),
                    ];
                })->sortBy('type')->toArray(),
                "canEdit"                      => $canEdit ? 1 : 0
            ];
        })->toArray();
        return $this->responseSuccess($data);
    }

    public function getClass(Request $request, GetUserRepository $getUserRepository)
    {
        $user_id = Auth::user()->id;
        $user_id = 2;
        $type    = AccessTypeEnum::TEACHER->value;


        $showUser = $getUserRepository->getUser($user_id, $type);
        if (!$showUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $classTeachers = $this->rollCallRepository->getClassTeacher($user_id);
        return $this->responseSuccess(
            $classTeachers

        );
    }


    public function studentInClass($class_id, $teacher_subject_timetable_id, Request $request)
    {
        // Lấy tham số name và student_code từ request
        $name         = $request->input('name', null);         // Tên học sinh
        $student_code = $request->input('student_code', null); // Mã học sinh
        $date         = isset($request->date) ? Carbon::parse($request->date) : Carbon::now();
        // Gọi repository để lấy danh sách học sinh theo lớp và tham số tìm kiếm
        $student = $this->rollCallRepository->getStudent($class_id, $teacher_subject_timetable_id, $date, $name,
            $student_code);
        // Kiểm tra và trả về kết quả
        if ($student) {
            return $this->responseSuccess($student, trans('api.rollcall.index.success'));
        } else {
            return $this->responseError(trans('api.rollcall.index.errors'));
        }
    }


    public function rollCall(Request $request, $classId, GetUserRepository $getUserRepository)
    {
        $user = Auth::user();
        if (!$user) {
            return $this->responseError(trans('api.error.user_not_logged_in'));
        }

        $user_id = $user->id;
        $type    = AccessTypeEnum::TEACHER->value;


        $showUser = $getUserRepository->getUser($user_id, $type);
        if (!$showUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $rollCallData                 = $request->input('rollcallData', []);
        $date                         = isset($request->date) ? Carbon::parse($request->date) : now();
        $teacher_subject_timetable_id = $request->teacher_subject_timetable_id;
        $this->rollCallRepository->attendanceStudentOfClass($teacher_subject_timetable_id, $classId, $rollCallData,
            $user_id, $date);

        return $this->responseSuccess();
    }


}
