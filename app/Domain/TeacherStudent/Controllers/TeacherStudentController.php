<?php
namespace App\Domain\TeacherStudent\Controllers;

use App\Common\Enums\AcademicTypeEnum;
use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\Student\Repository\StudentAddRepository;
use App\Domain\Student\Repository\StudentandParensRepository;
use App\Domain\Student\Repository\StudentDeleteRepository;
use App\Domain\Student\Repository\StudentIndexClassByYearRepository;
use App\Domain\Student\Repository\StudentRepository;
use App\Domain\Student\Repository\StudentUpdateRepository;
use App\Domain\Student\Repository\StudentUpGradeRepository;
use App\Domain\Student\Requests\AssignParentRequest;
use App\Domain\Student\Requests\StudentRequest;
use App\Domain\Student\Requests\StudentUpdateRequest;
use App\Domain\Student\Requests\TeacherStudentRequest;
use App\Domain\TeacherStudent\Repository\TeacherStudentRepository;
use App\Http\Controllers\BaseController;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;


class TeacherStudentController extends BaseController
{
    protected $studentRepository;
    private $user;


    public function __construct(StudentRepository $studentRepository, Request $request)
    {
        $this->studentRepository = $studentRepository;
        $this->user = new GetUserRepository();
        parent::__construct($request);
    }


    public function index(Request $request)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::TEACHER->value;

        // Kiểm tra quyền truy cập của người dùng
        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        // $request->validate([
        //     'class_id' => 'required',
        // ], [
        //     'required' => trans('api.error.required')
        // ]);


        // Lấy kích thước trang
        $pageSize = $request->input('pageSize', 10);
        if (!is_numeric($pageSize) || $pageSize <= 0) {
            // $pageSize = 10; // Mặc định về 10 bản ghi
            return response()->json(['message' => 'yêu cầu nhập số lượng lớn hơn 1']);
        }

        $studentRepository = new TeacherStudentRepository();

        // Lấy danh sách sinh viên
        $students = $studentRepository->paginateStudents($pageSize, $request->class_id);
        if ($students->count() > 0) {
            return response()->json([
                'status' => 'success',
                'data' => $students->items(), // Thay items() bằng all() ở đây
                'total' => $students->total(), // Tổng số bản ghi
                'page_index' => $students->currentPage(), // Trang hiện tại
                // 'page' => $students->lastPage(), // Trang cuối cùng
                'page_size' => $students->perPage(), // Số bản ghi mỗi trang
            ]);
        } else {
            return response()->json(['status' => 'error', 'data' => []]);
        }
    }


    public function store(TeacherStudentRequest $request) {
        $StudentAddRepository = new StudentAddRepository();
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;

        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $check = $StudentAddRepository->handle($user_id, $request);
        if ($check) {
            $student = Student::with(['classHistory' => function($query) {
                $query->select('student_id', 'class_id')
                      ->with(['class' => function($q) {
                          $q->select('id', 'name');
                      }]);
            }])->where('student_code', $request->student_code)->first();


            return response()->json([
                'message' => trans('api.alert.together.add_success'),
                'status' => 'success',
                'data' => []

            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => trans('api.alert.together.add_failed'),
                'data' => []
            ]);
        }
    }




}
