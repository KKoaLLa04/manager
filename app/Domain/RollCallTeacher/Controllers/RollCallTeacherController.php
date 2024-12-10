<?php
namespace App\Domain\RollCallTeacher\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\RollCallTeacher\Repository\RollCallTeacherRespository;
use App\Http\Controllers\BaseController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RollCallTeacherController extends BaseController
{
    protected $rollCallRepository;

    public function __construct(RollCallTeacherRespository $rollCallRepository)
    {
        $this->rollCallRepository = $rollCallRepository;
    }
    public function index(Request $request, GetUserRepository $getUserRepository)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::TEACHER->value;

        $showUser = $getUserRepository->getUser($user_id, $type);
        if (!$showUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $pageIndex = $request->input('pageIndex', 1); // Mặc định là trang 1
        $pageSize = $request->input('pageSize', 10); // Mặc định số bản ghi trên mỗi trang
        $keyWord = $request->input('keyWord', null); // Từ khóa tìm kiếm
        $date = $request->input('date', null); // Ngày điểm danh

        $rollCalls = $this->rollCallRepository->getClass($pageIndex, $pageSize, $keyWord, $date);

        if ($rollCalls) {
            return $this->responseSuccess($rollCalls, trans('api.rollcall.index.success'));
        } else {
            return $this->responseError(trans('api.rollcall.index.errors'));
        }
    }

    public function studentInClass($class_id, Request $request,GetUserRepository $getUserRepository)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::TEACHER->value;

        $showUser = $getUserRepository->getUser($user_id, $type);
        if (!$showUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        // Lấy tham số name và student_code từ request
        $name = $request->input('name', null); // Tên học sinh
        $student_code = $request->input('student_code', null); // Mã học sinh
    
        // Gọi repository để lấy danh sách học sinh theo lớp và tham số tìm kiếm
        $student = $this->rollCallRepository->getStudent($class_id, $name, $student_code);
    
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

        $rollCallData = $request->input('rollcallData', []);
        $date         = isset($request->date) ? Carbon::parse($request->date) : now();

        $this->rollCallRepository->attendanceStudentOfClass($classId, $rollCallData, $user_id, $date);

        return $this->responseSuccess([], trans('api.rollcall.attendaced.success'));
    }




    public function updateByClass(Request $request, $class_id, GetUserRepository $getUserRepository)
    {
        $user_id = Auth::user()->id;
        $type    = AccessTypeEnum::TEACHER->value;

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
}
            