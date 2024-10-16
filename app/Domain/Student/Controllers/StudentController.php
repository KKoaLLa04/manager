<?php
namespace App\Domain\Student\Controllers;

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
        if (!is_numeric($pageSize) || $pageSize <= 0) {
            // $pageSize = 10; // Mặc định về 10 bản ghi
            return response()->json(['message' => 'yêu cầu nhập số lượng lớn hơn 1']);
        }
    
        $studentRepository = new StudentRepository();

        // Lấy danh sách sinh viên
        $students = $studentRepository->paginateStudents($pageSize);
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

    public function store(StudentRequest $request) {
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
                'message' => 'Thêm học sinh thành công',
                'status' => 'success',
                'data' => []
              
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



   

    public function update(int $id, StudentUpdateRequest $request)
    {
        $StudentUpdateRepository = new StudentUpdateRepository();
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;
    
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
    

    public function show($id)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;
        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }
    
        // Gọi phương thức từ repository
        $student = $this->studentRepository->getStudentWithDetails($id);
        
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
    
        // Nhóm lịch sử lớp học theo school_year_name
        $classHistories = $student->classHistory;
    
        if ($classHistories && $classHistories->isNotEmpty()) {
            $groupedClassHistories = [];
    
            foreach ($classHistories as $history) {
                $schoolYearName = optional($history->class->schoolYearName)->name;
    
                if (!isset($groupedClassHistories[$schoolYearName])) {
                    $groupedClassHistories[$schoolYearName] = [
                        'school_year_name' => $schoolYearName,
                        'classes' => []
                    ];
                }
    
                $groupedClassHistories[$schoolYearName]['classes'][] = [
                    'start_date' => $history->start_date,
                    'end_date' => $history->end_date,
                    'status' => $history->status,
                    'class_name' => optional($history->class)->name, // class có thể null
                ];
            }
    
            $studentArray['class_history'] = array_values($groupedClassHistories);
        } else {
            $studentArray['class_history'] = [];
        }
    
        // Lọc thông tin phụ huynh để chỉ lấy các trường cần thiết
        $studentArray['parents'] = $student->parents->map(function($parent) {
            return [
                'id' => $parent->id,
                'fullname' => $parent->fullname,
                'username' => $parent->username,
                'phone' => $parent->phone,
                'code' => $parent->code,
                'gender' => $parent->gender,
                'email' => $parent->email,
                'dob' => $parent->dob,
            ];
        });
    
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
                // 'student' => $result['student'],
                // 'parent' => $result['parent'],
                // 'children_count' => $result['children_count'],
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
            return response()->json(['message' => 'Hủy gán không thành công ', 'status' => 'error']);
        }

        return response()->json([
            'message' => 'Hủy gán phụ huynh thành công',
            'status' => 'success',
            'data' => [],
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


    public function showParents(Request $request)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::MANAGER->value;
    
        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }
    
        $pageSize = $request->input('pageSize', 10);
        if (!is_numeric($pageSize) || $pageSize <= 0) {
            return response()->json(['message' => 'Yêu cầu nhập số lượng lớn hơn 0']);
        }
    
        // Gọi phương thức từ repository để lấy danh sách phụ huynh
        $parents = $this->studentRepository->getAllParentsWithChildrenCount($pageSize);
    
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
            return response()->json(['status' => 'error', 'data' => []]);
        }
    }
    
    

    
   







}