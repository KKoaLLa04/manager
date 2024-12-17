<?php
namespace App\Domain\LeaveRequestGuardian\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\LeaveRequestGuardian\Models\LeaveRequestGuardian;
use App\Domain\LeaveRequestGuardian\Repository\LeaveRequestGuardianRepository;
use App\Domain\LeaveRequestGuardian\Requests\LeaveRequestGuardianRequest;
use App\Http\Controllers\BaseController;
use App\Models\Student;
use App\Models\StudentClassHistory;
use App\Models\User;
use App\Models\UserStudent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class LeaveRequestGuardianController extends BaseController
{
    protected $LeaveRequestGuardianRepository;
    private $user;
    public function __construct(Request $request, LeaveRequestGuardianRepository $LeaveRequestGuardianRepository)
    {
        $this->LeaveRequestGuardianRepository = $LeaveRequestGuardianRepository;
        $this->user=  new GetUserRepository();
        parent::__construct($request);
    }
    public function index(Request $request)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::GUARDIAN->value;
        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }
        $pageSize = $request->input('pageSize', 10);
        if (!is_numeric($pageSize) || $pageSize <= 0) {
            return response()->json(['message' => 'Yêu cầu nhập số lượng lớn hơn 1'], 400);
        }

        $pageIndex = $request->input('pageIndex', 1);
        if (!is_numeric($pageIndex) || $pageIndex <= 0) {
            return response()->json(['message' => 'Yêu cầu nhập số trang lớn hơn 0'], 400);
        }

        $leaveRequests = $this->LeaveRequestGuardianRepository->getLeaveRequestByParent($user_id, $pageSize, $pageIndex);

        $customData = [
            'data' => $leaveRequests->items(), 
            'total' => $leaveRequests->total(),
            'pageIndex' => $leaveRequests->lastPage(),
            'pageSize' => $leaveRequests->perPage(),
        ];

        return $this->responseSuccess($customData);
    }

    public function store(LeaveRequestGuardianRequest $request, $student_id)
    {
        $user_id = Auth::user()->id;
    
        // Kiểm tra quyền truy cập của phụ huynh
        $type = AccessTypeEnum::GUARDIAN->value;
        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }
    
        // Kiểm tra học sinh có thuộc về phụ huynh này qua bảng user_student
        $student = UserStudent::where('student_id', $student_id)
            ->where('user_id', $user_id)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->first();
        
        if (!$student) {
            return $this->responseError(trans('api.error.invalid_student')); 
        }
    
        $currentDate = Carbon::now()->format('Y-m-d');
        $class = StudentClassHistory::where('student_id', $student_id)
            ->where('start_date', '<=', $currentDate)
            ->where(function ($query) use ($currentDate) {
                $query->where('end_date', '>=', $currentDate)
                      ->orWhereNull('end_date');
            })
            ->with('class') 
            ->first();
    
        if (!$class) {
            return $this->responseError(trans('api.error.student_class_not_found'));
        }
    
        $class_id = $class->class->id; 
        $class_name = $class->class->name; 
        // Lấy tên học sinh
        $studentName = Student::where('id', $student_id)->value('fullname');

        // Lấy tên phụ huynh
        $parentName = User::where('id', $user_id)->value('fullname');
    

        $request->validated();
    
        $leaveRequest = LeaveRequestGuardian::create([
            'student_id' => $student_id,
            'parent_user_id' => $user_id,
            'leave_date' => $request['leave_date'],
            'return_date' => $request['return_date'],
            'note' => $request['note'],
            'class_id' => $class_id, 
        ]);
    
        $leaveRequest->class_name = $class_name;
        $leaveRequest->student_name = $studentName;
        $leaveRequest->parent_name = $parentName;
        $leaveRequest->makeHidden(['updated_at', 'created_at']);
        return $this->responseSuccess($leaveRequest, trans('Gửi đơn thành công chờ xác nhận từ phía nhà trường.'));
    }
    
    public function cancel($id, )
    {
        $user_id = Auth::user()->id;
    
        // Kiểm tra quyền truy cập của phụ huynh
        $type = AccessTypeEnum::GUARDIAN->value;
        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }
        $result = $this->LeaveRequestGuardianRepository->cancelLeaveRequest($id);

        if ($result) {
            return $this->responseSuccess([], trans('api.success.leave_request_cancelled'));
        }

        return $this->responseError(trans('api.error.leave_request_not_found'));
    }

    public function sendTestEmail()
    {
        $toEmail = 'hoangdxph32103@fpt.edu.vn';  
        $subject = 'Hệ thống website sổ liên lạc điện tử học sinh techscholl';

        // Gửi email
        Mail::raw('Khánh Nhàn trốn học bỏ tiết đú đởn với bạn bè Lừa gạt thầy cô trong trường yêu cầu phụ huynh nhắc nhở con em thật kỹ', function ($message) use ($toEmail, $subject) {
            $message->to($toEmail)
                    ->subject($subject);
        });

        return 'Email đã được gửi!';
    }
    
}
            