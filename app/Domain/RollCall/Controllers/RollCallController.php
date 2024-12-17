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
use App\Models\DiemDanh;
use App\Models\StudentClassHistory;
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

        $date     = isset($date) ? Carbon::parse($date) : Carbon::now();
        $day      = $date->dayOfWeek;
        $dayQuery = 0;
        switch ($day) {
            case 1:
                $dayQuery = 2;
                break;
            case 2:
                $dayQuery = 3;
                break;
            case 3:
                $dayQuery = 4;
                break;
            case 4:
                $dayQuery = 5;
                break;
            case 5:
                $dayQuery = 6;
                break;
            case 6:
                $dayQuery = 7;
                break;
            case 0:
                $dayQuery = 8;
                break;
        }
        $timetables   = DiemDanh::query()->with([
            'classSubjectTeacher',
            'classSubjectTeacher.teacher',
            'classSubjectTeacher.subject',
            'class',
            'class.grade',
        ])->where('thu', $dayQuery)->get();
        $classIds     = $timetables->unique('class_id')->pluck('class_id')->toArray();
        $classes      = Classes::query()->whereIn('id', $classIds)->where('status',
            StatusEnum::ACTIVE->value)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->get();
        $timetableIds = $timetables->pluck('id')->toArray();
        $rollCalls    = RollCall::query()->whereIn('diemdanh_id', $timetableIds)->where('date',
            $date->toDateString())->get()->groupBy('diemdanh_id');
        $timetables   = $timetables->groupBy('class_id');
        $data         = [];
        foreach ($timetables as $key => $timetable) {
            $class      = $classes->where('id', $key)->first();
            $data[$key] = [
                'ClassId'   => $class->id,
                'ClassName' => $class->name,
            ];
            $timetable  = $timetable->groupBy('buoi');
            foreach ($timetable as $keytime => $item) {
                $data[$key]['timetable'][$keytime] = [
                    $item->map(function ($item) use ($rollCalls, $class) {
                        $rollCall = $rollCalls->get($item->id);
                        return [
                            "idTimetable"     => $item->id,
                            "tiet"            => $item->tiet,
                            "mon"            =>     $item->classSubjectTeacher->subject->name,
                            "checkAttendance" => $this->rollCallRepository->checkAttendanceLog($class->id,
                                $item->id) ? 1 : 0,
                            "userId"          => is_null($item->classSubjectTeacher) ? 0 : $item->classSubjectTeacher->teacher->id,
                            "fullname"        => is_null($item->classSubjectTeacher) ? 0 : $item->classSubjectTeacher->teacher->fullname ?? "",
                            "email"           => is_null($item->classSubjectTeacher) ? 0 : $item->classSubjectTeacher->teacher->email ?? "",
                            "rollcall"        => is_null($rollCall) ? [
                                'totalRollCall' => 0,
                                'totalStudent'  => $this->classRepository->getStudentOfClass($class->id)->count(),
                            ] : [
                                'totalRollCall' => $rollCall->count(),
                                'totalStudent'  => $this->classRepository->getStudentOfClass($class->id)->count(),
                            ]

                        ];
                    })->toArray()
                ];
            }
        }

        return $this->responseSuccess($data);
    }


    public function studentInClass($class_id, $diemdanh_id, Request $request)
    {
        // Lấy tham số name và student_code từ request
        $name         = $request->input('name', null);         // Tên học sinh
        $student_code = $request->input('student_code', null); // Mã học sinh

        // Gọi repository để lấy danh sách học sinh theo lớp và tham số tìm kiếm
        $student = $this->rollCallRepository->getStudent($class_id, $diemdanh_id, $name, $student_code);
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
        $diemdanhId   = $request->diemdanh_id;
        $this->rollCallRepository->attendanceStudentOfClass($diemdanhId, $classId, $rollCallData, $user_id, $date);

        return $this->responseSuccess([], trans('api.rollcall.attendaced.success'));
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
