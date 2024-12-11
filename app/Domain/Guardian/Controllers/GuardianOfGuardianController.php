<?php

namespace App\Domain\Guardian\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\StatusClassStudentEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\AcademicYear\Models\AcademicYear;
use App\Domain\Guardian\Models\Guardian;
use App\Domain\Guardian\Repository\GuardianOfGuardianRespository;
use App\Domain\Guardian\Requests\GuardianLayoutGuardianRequest;
use App\Http\Controllers\BaseController;
use App\Models\Classes;
use App\Models\Student;
use App\Models\StudentClassHistory;
use App\Models\UserStudent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class GuardianOfGuardianController extends BaseController
{
    protected $guardianRepository;
    public function __construct(Request $request)
    {
        $this->guardianRepository = new GuardianOfGuardianRespository();
    }

    public function show(GetUserRepository $getUserRepository)
    {
        // Lấy ID của người dùng hiện tại
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::GUARDIAN->value;

        // Kiểm tra xem người dùng có quyền truy cập hay không
        $getUser = $getUserRepository->getUser($user_id, $type);
        if (!$getUser) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        // Lấy thông tin của Guardian
        $showoneGuardian = $this->guardianRepository->getOneGuardian();

        // Kiểm tra nếu không tìm thấy Guardian
        if ($showoneGuardian) {
            return $this->responseSuccess($showoneGuardian, trans('api.guardianofguardian.show.success'));
        } else {
            return $this->responseError(trans('api.guardianofguardian.show.errors'));
        }
    }

    public function getStudentInGuardian()
{
    $response = $this->guardianRepository->getStudentInGuardian();
    return response()->json($response);
}



    public function ChangePasswordGuardian(Request $request) {

        $request->validate([
            'id' => 'required',
            'password' => 'required',
            'confirm_password' => 'required',
        ]);


        $item = Guardian::find((int) $request->id);

        $item->password = Hash::make((string) $request->password);

        if ($item->save()) {

            return response()->json([
                'msg' => "Đổi mật khẩu thành công",
                'data' => []
            ], 200);

        }


    }


    public function layDanhSachHocSinh (Request $request) {

        $request->validate([
            'parent_id' => 'required'
        ]);


        $userStudent = UserStudent::where('user_id', $request->parent_id)->get();

        if ($userStudent) {
            return response()->json([
                'msg' => "Lấy danh sách học sinh thành công",
                'data' => $userStudent->map(function ($item) {
                    $student = Student::find($item->student_id);
                    // dd($student);
                    return [
                        'id' => $student->id,
                        'name' => $student->fullname,
                    ];
                })
            ], 200);
        }else {
            return response()->json([
                'msg' => "Lấy danh sách học sinh thành công",
                'data' => []
            ], 200);
        }


    }


    public function layMotHocSinh (Request $request) {

        $request->validate([
            'student_id' => 'required'
        ]);

        $item = Student::find($request->student_id);

        if ($item) {

            $history = StudentClassHistory::where('student_id', $request->student_id)->where('status', StatusClassStudentEnum::STUDYING->value)->first();

            $class = null;
            $nienkhoa = null;

            if ($history) {

                $class = Classes::find($history->class_id);

                $nienkhoa = AcademicYear::find($class->academic_year_id);

            }


            return response()->json([
                'msg' => 'Lấy chi tiết học sinh thành công',
                'data' => [
                    'name' => $item->fullname,
                    'code' => $item->student_code,
                    'sex' => $item->gender,
                    'date' => strtotime($item->dob),
                    'class' => $class ? $class->name : '',
                    'academic_year' => $nienkhoa ? $nienkhoa->name : '',
                    'phone' => $item->phone,
                    'status' => $item->status,
                    'address' => $item->address,
                ]
            ], 200);

        }


    }



}
