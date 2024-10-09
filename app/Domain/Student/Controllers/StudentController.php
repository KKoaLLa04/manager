<?php
namespace App\Domain\Student\Controllers;

use App\Common\Enums\AcademicTypeEnum;
use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\Student\Repository\StudentAddRepository;
use App\Domain\Student\Repository\StudentDeleteRepository;
use App\Domain\Student\Repository\StudentIndexClassByYearRepository;
use App\Domain\Student\Repository\StudentRepository;
use App\Domain\Student\Repository\StudentUpdateRepository;
use App\Domain\Student\Repository\StudentUpGradeRepository;
use App\Domain\Student\Requests\AssignParentRequest;
use App\Domain\Student\Requests\StudentRequest;
use App\Domain\Student\Requests\StudentUpdateRequest;
use App\Http\Controllers\BaseController;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;



class StudentController extends BaseController
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
        $type = AccessTypeEnum::MANAGER->value;

        // Kiểm tra quyền truy cập của người dùng
        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        // Lấy kích thước trang
        $pageSize = $request->input('pageSize', 10);

        $studentRepository = new StudentRepository();

        // Lấy danh sách sinh viên
        $students = $studentRepository->paginateStudents($pageSize);

        // Kiểm tra nếu có sinh viên
        if ($students->count() > 0) {
            return response()->json([
                'status' => 'success',
                'data' => $students->items(), // Thay items() bằng all() ở đây
                'total' => $students->total(), // Tổng số bản ghi
                'current_page' => $students->currentPage(), // Trang hiện tại
                'last_page' => $students->lastPage(), // Trang cuối cùng
                'per_page' => $students->perPage(), // Số bản ghi mỗi trang
            ]);
        } else {
            return response()->json(['status' => 'error', 'data' => []]);
        }
    }



    public function store(StudentRequest $request) {
        $StudentAddRepository = new StudentAddRepository();
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;

        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $check = $StudentAddRepository->handle($user_id, $request);

        if ($check) {

            return response()->json([
                'message' => 'Thêm học sinh thành công',
                'status' => 'success',
                'data' => $request->all()
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Thêm học sinh thất bại',
                'data' => []
            ]);
        }
    }

    public function delete(int $id, ){


        $user_id =Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;

        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $StudentDeleteRepository = new StudentDeleteRepository();

        $check = $StudentDeleteRepository->handle($id, $user_id);
        if ($check) {
            $data = Student::find($id);
            return response()->json([
                'message' => 'Xóa học sinh ' . $data->fullname . ' thành công',
                'status' => 'success',

            ]);
        } else {
            $data = Student::find($id);
            return response()->json([
                'status' => 'error',
                'message' => 'Xóa học sinh thất bại',
                'data' => []
            ]);
        }
    }



    public function update(int $id, StudentUpdateRequest $request){

        $StudentUpdateRepository = new StudentUpdateRepository();
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;
        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $check = $StudentUpdateRepository->handle($id, $user_id, $request);
        if ($check) {
            $data = Student::find($id);

            // Chuyển đổi đối tượng thành mảng
            $studentArray = $data->toArray();
            $studentArray['class_id'] = optional($data->classHistory)->class_id;

            return response()->json([
                'message' => 'Sửa học sinh ' . $studentArray['fullname'] . ' thành công',
                'status' => 'success',
                'data' => [
                    'student' => $studentArray
                ]
            ]);
        } else {
            return response()->json([
                'message' => 'Sửa học sinh thất bại',
                'status' => 'error',
                'data' => []
            ]);
        }
    }
    // public function show($id)
    // {
    //     $user_id = Auth::user()->id;
    //     $type = AccessTypeEnum::MANAGER->value;

    //     if (!$this->user->getUser($user_id, $type)) {
    //         return $this->responseError(trans('api.error.user_not_permission'));
    //     }
    //     $student = Student::with('classHistory.class')->find($id);

    //     // Kiểm tra nếu học sinh không tồn tại
    //     if (!$student) {
    //         return response()->json([
    //             'message' => 'Không tìm thấy học sinh',
    //             'status' => 'error',
    //             'data' => []
    //         ]);
    //     }

    //     // Chuyển đối tượng Student thành mảng và thêm thông tin class
    //     $studentArray = $student->toArray();
    //     $studentArray['class_id'] = optional($student->classHistory)->class_id;
    //     $studentArray['class_name'] = optional($student->classHistory->class)->name; // Lấy tên của lớp học nếu tồn tại

    //     return response()->json([
    //         'message' => 'Lấy thông tin học sinh thành công',
    //         'status' => 'success',
    //         'data' => $studentArray
    //     ]);
    // }
    public function show($id)
    {
        $student = Student::with(['classHistory' => function($query) {
            // Chỉ lấy các trường cần thiết từ StudentClassHistory và lấy thêm thông tin lớp học
            $query->select('student_id', 'class_id', 'start_date', 'end_date', 'status')
                  ->with(['class' => function($q) {
                      // Chỉ lấy class_id và name của lớp
                      $q->select('id', 'name');
                  }]);
        }, 'parents' => function($query) {
            // Chỉ lấy các trường cần thiết từ phụ huynh
            $query->select('users.id', 'fullname', 'phone', 'code', 'gender', 'email', 'dob')
                  ->where('users.access_type', AccessTypeEnum::GUARDIAN->value)
                  ->where('users.is_deleted', DeleteEnum::DELETED->value);
        }])
        ->where('is_deleted', DeleteEnum::DELETED->value) // Kiểm tra xem học sinh có bị xóa không
        ->find($id);

        if (!$student) {
            return response()->json([
                'message' => 'Học sinh này không tồn tại',
                'status' => 'error',
                'data' => []
            ]);
        }

        $studentArray = $student->toArray();

        // Thêm thông tin cần thiết từ classHistory
        $classHistory = $student->classHistory;
        $studentArray['start_date'] = optional($classHistory)->start_date;
        $studentArray['end_date'] = optional($classHistory)->end_date;
        $studentArray['class_history_status'] = optional($classHistory)->status;

        // Kiểm tra xem classHistory có tồn tại không trước khi lấy class_id và class_name
        if ($classHistory) {
            $studentArray['class_id'] = optional($classHistory->class)->id;  // class có thể null
            $studentArray['class_name'] = optional($classHistory->class)->name; // class có thể null
        } else {
            $studentArray['class_id'] = null;
            $studentArray['class_name'] = null;
        }

        // Thêm thông tin phụ huynh vào mảng học sinh
        $studentArray['parents'] = $student->parents ?? []; // Đảm bảo có giá trị mặc định là mảng rỗng nếu không có phụ huynh

        return response()->json([
            'message' => 'Lấy thông tin học sinh thành công',
            'status' => 'success',
            'data' => $studentArray
        ]);
    }







    public function assignParent(int $student_id, AssignParentRequest $request)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;

        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $parent_id = $request->input('parent_id');

        // Gọi phương thức gán phụ huynh từ repository
        $result = $this->studentRepository->assignParentToStudent($student_id, $parent_id);

        if (isset($result['error'])) {
            return response()->json(['message' => $result['error'], 'status' => 'error']);
        }

        return response()->json([
            'message' => 'Gán phụ huynh thành công',
            'status' => 'success',
            'data' => [
                'student' => $result['student'],
                'parent' => $result['parent'],
                'children_count' => $result['children_count'],
            ],
        ]);
    }

    public function detachParent(int $student_id, Request $request)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;

        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $parent_id = $request->input('parent_id');

        // Gọi phương thức hủy gán phụ huynh từ repository
        $result = $this->studentRepository->detachParentFromStudent($student_id, $parent_id);

        // Kiểm tra xem có lỗi trong kết quả không
        if (isset($result['error'])) {
            return response()->json(['message' => $result['error'], 'status' => 'error']);
        }

        if (!$result) {
            return response()->json(['message' => 'Hủy gán không thành công', 'status' => 'error']);
        }

        return response()->json([
            'message' => 'Hủy gán phụ huynh thành công',
            'status' => 'success',
            'data' => [
                'student' => $result['student'],
                'parent_id' => $result['parent_id'],
                'children_count' => $result['children_count'],
            ],
        ]);

    }


    public function upGrade (Request $request) {


        $user_id = Auth::user()->id;

        if(!$this->user->getManager($user_id)){
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $validator = Validator::make($request->all(), [
            'class_id' => 'required|integer',
        ], [
            'integer' => trans('api.error.integer'),
            'required' => trans('api.error.required'),
        ]);

        $errors = $validator->errors()->messages();

        $checkError = false;

        if(empty($request->students) || !is_array($request->students)){
            $errors['students'] = [
                trans('api.error.student.students_array_required')
            ];
            $checkError = true;
        }

        // Nếu có lỗi trong validation hoặc lỗi tùy chỉnh
        if ($validator->fails() || $checkError) {
            // Tùy chỉnh mảng lỗi để trả về
            $customErrors = [];
            foreach ($errors as $field => $messages) {
                foreach ($messages as $message) {
                    $customErrors[$field] = [
                        $message,
                    ];
                }
            }

            // Trả về phản hồi JSON với lỗi
            return response()->json([
                'errors' => $customErrors,
            ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);

        }


        $repository = new StudentUpGradeRepository();

        $check = $repository->handle($user_id, $request->students, $request);

        if($check){
            return $this->responseSuccess([], trans('api.alert.together.add_success'));
        }else{
            return $this->responseError(trans('api.alert.together.add_failed'));
        }



    }



    public function classByYear (Request $request) {

        $user_id = Auth::user()->id;

        if(!$this->user->getManager($user_id)){
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $request->validate([
            'school_year_id' => 'required|integer',
        ], [
            'integer' => trans('api.error.integer'),
            'required' => trans('api.error.required'),
        ]);


        $repository = new StudentIndexClassByYearRepository();

        $check = $repository->handle($request->school_year_id);

        return $this->responseSuccess($check, trans('api.alert.together.index_success'));

    }











}
