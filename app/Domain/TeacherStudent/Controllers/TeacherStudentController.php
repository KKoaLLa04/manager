<?php
namespace App\Domain\TeacherStudent\Controllers;

use App\Common\Enums\AcademicTypeEnum;
use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
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
use App\Domain\TeacherStudent\Repository\TeacherStudentAddRepository;
use App\Domain\TeacherStudent\Requests\TeacherStudentRequest;
use App\Domain\TeacherStudent\Repository\TeacherStudentRepository;
use App\Domain\TeacherStudent\Repository\TeacherStudentUpdateRepository;
use App\Domain\TeacherStudent\Requests\TeacherStudentUpdateRequest;
use App\Http\Controllers\BaseController;
use App\Models\Classes;
use App\Models\Student;
use App\Models\StudentClassHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use App\Models\UserStudent;


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
            return response()->json(['status' => 'success', 'data' => []]);
        }
    }


    public function store(TeacherStudentRequest $request) {
        $StudentAddRepository = new TeacherStudentAddRepository();
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::TEACHER->value;

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


    public function show($id)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::TEACHER->value;
        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $repository = new TeacherStudentRepository();

        // Gọi phương thức từ repository
        // $student = $this->studentRepository->getStudentWithDetails($id);
        $student = $repository->getStudentWithDetails($id);

        // Kiểm tra nếu không tìm thấy học sinh
        if (!$student) {
            return response()->json([
                'message' => 'Học sinh này không tồn tại',
                'status' => 'error',
                'data' => []
            ]);
        }

        // Chuyển đổi dữ liệu học sinh thành mảng
        $studentArray = $student->toArray();

        $class = null;

        $studentHistory = StudentClassHistory::where('student_id', $student->id)->where('status', StatusEnum::ACTIVE->value)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->first();

        if($studentHistory){

            $class = Classes::find($studentHistory->class_id);

        }

        $parent = null;

        $userStudent =  UserStudent::where('student_id', $student->id)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->first();

        if ($userStudent) {
            $parent = User::find($userStudent->id);
        }

        unset($studentArray['parents']);
        unset($studentArray['class_history']);


        $studentArray['current_academic_year_name'] = $class ? $class->schoolYear->name : "";
        $studentArray['current_class_name'] = $class ? $class->name : "";


        $studentArray['parents_id'] = $parent ? $parent->id : "";
        $studentArray['parents_name'] = $parent ? $parent->fullname : "";
        $studentArray['parents_phone'] = $parent ? $parent->phone : "";
        $studentArray['parents_code'] = $parent ? $parent->code : "";
        $studentArray['parents_gender'] = $parent ? $parent->gender : "";
        $studentArray['parents_email'] = $parent ? $parent->email : "";
        $studentArray['parents_dob'] = $parent ? strtotime($parent->dob) : "";
        $studentArray['parents_address'] = $parent ? $parent->address : "";

        return response()->json([
            'message' => 'Lấy thông tin học sinh thành công',
            'status' => 'success',
            'data' => $studentArray
        ]);
    }


    public function update(int $id, TeacherStudentUpdateRequest $request)
    {
        $StudentUpdateRepository = new TeacherStudentUpdateRepository();
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::TEACHER->value;

        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        // Thực hiện cập nhật thông qua repository
        $check = $StudentUpdateRepository->handle($id, $user_id, $request);

        if ($check) {
            $data = Student::find($id);

            // Chuyển đổi đối tượng thành mảng
            $studentArray = $data->toArray();

            // Không tạo bản ghi mới nếu class_id không thay đổi
            $studentArray['class_id'] = optional($data->classHistory->last())->class_id;

            return response()->json([
                'message' => 'Sửa học sinh ' . $studentArray['fullname'] . ' thành công',
                'status' => 'success',
                'data' => []
            ]);
        } else {
            return response()->json([
                'message' => 'Sửa học sinh thất bại',
                'status' => 'error',
                'data' => []
            ]);
        }
    }


    public function showParents(Request $request)
    {

        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::TEACHER->value;

        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $pageSize = $request->input('pageSize', 10);
        if (!is_numeric($pageSize) || $pageSize <= 0) {
            return response()->json(['message' => 'Yêu cầu nhập số lượng lớn hơn 0']);
        }

        $studentRepository = new TeacherStudentRepository();
        // Gọi phương thức từ repository để lấy danh sách phụ huynh
        $parents = $studentRepository->getAllParentsWithChildrenCount($pageSize);

        if ($parents->count() > 0) {
            return response()->json([
                'message' => 'Lấy danh sách phụ huynh thành công',
                'status' => 'success',
                'data' => $parents->items(), // Lấy danh sách phụ huynh
                'total' => $parents->total(), // Tổng số bản ghi
                'page_index' => $parents->currentPage(), // Trang hiện tại
                'page_size' => $parents->perPage(), // Số bản ghi mỗi trang
            ]);
        } else {
            return response()->json(['status' => 'success', 'data' => []]);
        }
    }

    public function detachParent(int $student_id, Request $request)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::TEACHER->value;

        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $parent_id = $request->input('parent_id');

        $studentRepository = new TeacherStudentRepository();
        // Gọi phương thức hủy gán phụ huynh từ repository
        $result = $studentRepository->detachParentFromStudent($student_id, $parent_id);

        // Kiểm tra xem có lỗi trong kết quả không
        if (isset($result['error'])) {
            return response()->json(['message' => $result['error'], 'status' => 'error']);
        }

        if (!$result) {
            return response()->json(['message' => 'Hủy gán không thành công ', 'status' => 'error']);
        }

        return response()->json([
            'message' => 'Hủy gán phụ huynh thành công',
            'status' => 'success',
            'data' => [],
        ]);

    }

    public function assignParent(int $student_id, AssignParentRequest $request)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::TEACHER->value;

        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $parent_id = $request->input('parent_id');


        $studentRepository = new TeacherStudentRepository();

        // Gọi phương thức gán phụ huynh từ repository
        $result = $studentRepository->assignParentToStudent($student_id, $parent_id);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error'], 'status' => 'error']);
        }

        return response()->json([
            'message' => 'Gán phụ huynh thành công',
            'status' => 'success',
            'data' => [
                // 'student' => $result['student'],
                // 'parent' => $result['parent'],
                // 'children_count' => $result['children_count'],
            ],
        ]);
    }

}
