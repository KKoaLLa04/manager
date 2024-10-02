<?php
namespace App\Domain\Student\Controllers;

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
            // $request->validate([
            //     'user_id' => [
            //         'required',
            //         'integer'
            //     ],
            //     'type' => [
            //         'required',
            //         'integer'
            //     ],
            // ]);
    
            // $user_id = $request->user_id;
            // $type = $request->type;
    
            // if (!$this->user->getUser($user_id, $type)) {
            //     return $this->responseError(trans('api.error.user_not_permission'));
            // }
    
            $studentRepository = new StudentRepository();
    
            $check = $studentRepository->handle();
    
            if ($check) {
                return response()->json(['status' => 'success', 'data' => $check->toArray()]);
            } else {
                return response()->json(['status' => 'error', 'data' => []]);
            }
        
    }

    public function store(StudentRequest $request){
        $StudentAddRepository = new StudentAddRepository();
        $user_id = $request->user_id;
        $type = $request->type;

        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $check = $StudentAddRepository->handle($user_id, $request);

        if ($check) {
            $data = Student::all();
            $data = $data->last();
            return response()->json([
                'message' => 'Thêm học sinh ' . $data->fullname . ' thành công',
                'status' => 'success',
                'data' => $data
            ]);
        } else {
            $data = Student::all();
            $data = $data->last();
            return response()->json([
                'status' => 'error',
                'message' => 'Thêm học sinh thất bại',
                'data' => []
            ]);
        }
    
    }

    public function delete(int $id, Request $request){
        $request->validate([
            'user_id' => [
                'required',
                'integer'
            ],
            'type' => [
                'required',
                'integer'
            ],
        ]);

        $user_id = $request->user_id;
        $type = $request->type;

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
        $user_id = $request->user_id;
        $type = $request->type;
        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $check = $StudentUpdateRepository->handle($id, $user_id, $request);

        if ($check) {
            $data = Student::find($id);
            return response()->json(['message' => 'Sửa học sinh ' . $data->name . ' thành công', 'status' => 'success', 'data' => $data]);
        } else {
            $data = Student::find($id);
            return response()->json(['message' => 'Sửa học sinh ' . $data->name . ' thất bại', 'status' => 'error', 'data' => []]);
            // return 0;
        }
    }
    
    
}
            