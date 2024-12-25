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

class RollCallController extends BaseController
{

    protected $rollCallRepository;

    public function __construct(
        RollCallRepository        $rollCallRepository,
        protected ClassRepository $classRepository,
    ) {
        $this->rollCallRepository = $rollCallRepository;
    }

    public function index(Request $request, GetUserRepository $getUserRepository)
    {
        $user_id = Auth::user()->id;
        $type    = AccessTypeEnum::MANAGER->value;

        $showUser = $getUserRepository->getUser($user_id, $type);
        if (!$showUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }
        $classId  = $request->classId;
        $date     = isset($request->date) ? Carbon::parse(1734971033) : Carbon::now();
        $dayQuery      = $date->dayOfWeek;

        $class = $this->rollCallRepository->getClassById($classId);
        if (is_null($class)) {
            return $this->responseSuccess([]);
        }
        $timetables                  = Timetable::query()->where('day', $dayQuery)->get();
        $timetableIds                = $timetables->pluck('id')->toArray();
        $getClassSubjectTeachers     = $this->rollCallRepository->getClassSubjectTeacher($classId);
        $classSubjectTeacherIds      = $getClassSubjectTeachers->pluck('id')->toArray();
        $getTeacherSubjectTimetables = $this->rollCallRepository->getTeacherSubjectTimetable($classSubjectTeacherIds,
            $timetableIds);
        $studentIds                  = $this->rollCallRepository->getStudentInClass($classId);
        $data = $getTeacherSubjectTimetables->map(function ($teacherSubjectTimetable) use (
            $timetables,
            $getClassSubjectTeachers,
            $studentIds,
            $date
        ) {
            $rollcalls              = $teacherSubjectTimetable->rollcalls;
            $totalChecked = $rollcalls->whereIn('student_id',$studentIds)->count();
            $attendanceLogs          = $this->rollCallRepository->getAttendanceLog($teacherSubjectTimetable->id,
                $teacherSubjectTimetable->class_id, $date);
            $getClassSubjectTeacher = $getClassSubjectTeachers->where('id', $teacherSubjectTimetable->class_subject_teacher_id)->first();
            $timetable              = $timetables->where('id', $teacherSubjectTimetable->timetable_id)->first();
            $from_time = Carbon::parse($timetable->from_time);
            $to_time = Carbon::parse($timetable->to_time);
            return [
                'teacher_subject_timetable_id' => $teacherSubjectTimetable->id,
                'timetable_id'        => $timetable->id,
                'timetable_time'      => $timetable->time,
                'timetable_period'    => $timetable->period,
                'timetable_from_time' => $from_time->translatedFormat('H:i'),
                'timetable_to_time'   =>  $to_time->translatedFormat('H:i'),
                'teacher_id'          => $getClassSubjectTeacher->teacher->id,
                'teacher_name'        => $getClassSubjectTeacher->teacher->fullname,
                'teacher_email'       => $getClassSubjectTeacher->teacher->email,
                'subject_id'          => $getClassSubjectTeacher->subject->id,
                'subject_name'        => $getClassSubjectTeacher->subject->name,
                'totalChecked'        => $totalChecked,
                'totalStudent'        => count($studentIds),
                'attendance_checked'  => $attendanceLogs->isEmpty() ? 0 : 1,
                'attendance_histories'=> $attendanceLogs->map(function ($attendanceLog) {
                    return [
                        'user_name' => $attendanceLog->user->fullname,
                        'type' => $attendanceLog->type,
                        'time' => Carbon::parse($attendanceLog->time)->format('d-m-Y H:i:s'),
                    ];
                })->sortBy('type')->toArray(),
            ];
        })->toArray();
        return $this->responseSuccess($data);
    }


    public function studentInClass($class_id, $teacher_subject_timetable_id, Request $request)
    {
        // Lấy tham số name và student_code từ request
        $name         = $request->input('name', null);         // Tên học sinh
        $student_code = $request->input('student_code', null); // Mã học sinh
        $date         = isset($request->date) ? Carbon::parse($request->date) : Carbon::now();
        // Gọi repository để lấy danh sách học sinh theo lớp và tham số tìm kiếm
        $student = $this->rollCallRepository->getStudent($class_id, $teacher_subject_timetable_id, $date, $name, $student_code);
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
        $type    = AccessTypeEnum::MANAGER->value;


        $showUser = $getUserRepository->getUser($user_id, $type);
        if (!$showUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $rollCallData = $request->input('rollcallData', []);
        $date         = isset($request->date) ? Carbon::parse($request->date) : now();
        $teacher_subject_timetable_id   = $request->teacher_subject_timetable_id;
        $this->rollCallRepository->attendanceStudentOfClass($teacher_subject_timetable_id, $classId, $rollCallData, $user_id, $date);

        return $this->responseSuccess();
    }

    public function updateByClass(Request $request, $class_id, GetUserRepository $getUserRepository)
    {
        $user_id = Auth::user()->id;
        $type    = AccessTypeEnum::MANAGER->value;

        $showUser = $getUserRepository->getUser($user_id, $type);
        if (!$showUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $validatedData = $request->validate([
            'students'              => 'required|array',
            'students.*.student_id' => 'required|integer|exists:students,id',
            'students.*.status'     => 'required|integer',
            'students.*.note'       => 'nullable|string',
        ]);

        [
            $totalStudent,
            $totalStudentAttendaced,
            $totalStudentNotAttendaced,
            $rollCalls
        ] = $this->rollCallRepository->updateByClass($class_id, $validatedData['students'], $user_id);
        $data = [
            'total_student'              => $totalStudent,
            'total_student_attended'     => $totalStudentAttendaced,
            'total_student_not_attended' => $totalStudentNotAttendaced,
            'updated_roll_calls'         => $rollCalls,
        ];

        return $this->responseSuccess($data, trans('api.rollcall.attendaced_updated.success'));
    }

    public function getRowCallOfClass(RollCallRequest $request)
    {
        $keyWord = $request->input('keyWord', "");
        $classId = $request->input('classId', 0);
        $date    = $request->input('date');
        $date    = isset($date) ? Carbon::parse($date) : now();
        list($students, $total) = $this->rollCallRepository->getStudentClass($classId, $keyWord);
        $rollCall = $this->rollCallRepository->getRollCall($classId, $students, $date);
        return $this->responseSuccess(
            [
                "rollCall"     => $rollCall,
                "totalStudent" => $total,
            ]
        );
    }
}
