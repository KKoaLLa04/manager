<?php
namespace App\Domain\Student\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\Student\Repository\StudentAddRepository;
use App\Domain\Student\Repository\StudentDeleteRepository;
use App\Domain\Student\Repository\StudentRepository;
use App\Domain\Student\Repository\StudentUpdateRepository;
use App\Domain\Student\Requests\StudentRequest;
use App\Domain\Student\Requests\StudentUpdateRequest;
use App\Http\Controllers\BaseController;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentController extends BaseController
{
    protected $studentRepository;
    private $user;
    public function __construct(Request $request)
    {
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
        $pageSize = $request->get('pageSize', 10);  
    
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
    
    
}
            