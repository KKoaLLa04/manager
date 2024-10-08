<?php
namespace App\Domain\Student\Controllers;

use App\Common\Enums\AcademicTypeEnum;
use App\Common\Enums\AccessTypeEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\Student\Repository\StudentAddRepository;
use App\Domain\Student\Repository\StudentDeleteRepository;
use App\Domain\Student\Repository\StudentRepository;
use App\Domain\Student\Repository\StudentUpdateRepository;
use App\Domain\Student\Requests\AssignParentRequest;
use App\Domain\Student\Requests\StudentRequest;
use App\Domain\Student\Requests\StudentUpdateRequest;
use App\Http\Controllers\BaseController;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function show($id){
        $student = Student::with(['classHistory' => function($query) {
            // Chỉ lấy các trường cần thiết từ StudentClassHistory và lấy thêm thông tin lớp học
            $query->select('student_id', 'class_id', 'start_date', 'end_date', 'status')
                  ->with(['class' => function($q) {
                      // Chỉ lấy class_id và name của lớp
                      $q->select('id', 'name');
                  }]);
        }])->find($id);

            if (!$student) {
            return response()->json([
                'message' => 'Không tìm thấy học sinh',
                'status' => 'error',
                'data' => []
            ]);
        }
    
        $studentArray = $student->toArray();
        
        // Thêm thông tin cần thiết từ classHistory
        $studentArray['start_date'] = optional($student->classHistory)->start_date;
        $studentArray['end_date'] = optional($student->classHistory)->end_date;
        $studentArray['class_history_status'] = optional($student->classHistory)->status;
        $studentArray['class_id'] = optional($student->classHistory->class)->id;
        $studentArray['class_name'] = optional($student->classHistory->class)->name;
    
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
    
    
    
    


    
    
    
    
    

    
    
}
            