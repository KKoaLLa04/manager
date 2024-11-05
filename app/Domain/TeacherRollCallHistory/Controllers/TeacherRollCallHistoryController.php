<?php
namespace App\Domain\TeacherRollCallHistory\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\GenderEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Repository\GetUserRepository;
use App\Domain\TeacherRollCallHistory\Repository\TeacherRollCallHistoryRepository;
use App\Http\Controllers\BaseController;
use App\Models\Classes;
use App\Models\ClassSubjectTeacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherRollCallHistoryController extends BaseController
{
    protected $TeacherRollCallHistoryRepository;
    private $user;
    public function __construct(Request $request, TeacherRollCallHistoryRepository $teacherRollCallHistoryRepository)
    {
        $this->user=  new GetUserRepository();
        parent::__construct($request);
        $this->TeacherRollCallHistoryRepository = $teacherRollCallHistoryRepository;
    }

    public function index(Request $request)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::TEACHER->value;

        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        $pageSize = $request->input('pageSize', 10);
        if (!is_numeric($pageSize) || $pageSize <= 0) {
            return response()->json(['message' => 'Yêu cầu nhập số lượng lớn hơn 0'], 400);
        }
        $keyWord = $request->input('keyWord', null);

        $classes = $this->TeacherRollCallHistoryRepository->getClassesWithRollCallHistories($user_id, $pageSize, $keyWord);

        return response()->json([
            'message' => 'Lấy danh sách lớp thành công',
            'status' => 'success',
            'total_classes' => $classes['total'],
            'data' => $classes['data'],
            'page_index' => $classes['current_page'],
            'page_size' => $classes['per_page'],
        ]);
    }

    


    public function showclass(Request $request, $classId)
{
    $user_id = Auth::user()->id;
    $type = AccessTypeEnum::TEACHER->value;
    if (!$this->user->getUser($user_id, $type)) {
        return $this->responseError(trans('api.error.user_not_permission'));
    }

    // Kiểm tra xem lớp có thuộc về giáo viên không
    $isTeachingClass = ClassSubjectTeacher::where('class_id', $classId)
        ->where('user_id', $user_id)
        ->exists();

    if (!$isTeachingClass) {
        return response()->json(['message' => 'Bạn không có quyền xem lớp này'], 403); 
    }

    // Xử lý số lượng bản ghi trên mỗi trang
    $pageSize = $request->input('pageSize', 10);
    if (!is_numeric($pageSize) || $pageSize <= 0) {
        return response()->json(['message' => 'Yêu cầu nhập số lượng lớn hơn 0'], 400);
    }

    $keyWord = $request->input('keyWord', null);
    $date = $request->input('date', null);

    // Lấy lịch sử điểm danh
    $histories = $this->TeacherRollCallHistoryRepository->getTeacherClassRollCallHistories($classId, $pageSize, $keyWord, $date);

    return response()->json($histories);
}

    


    public function showClassTeacher(Request $request, $classId)
    {
        $user_id = Auth::user()->id;
        $type = AccessTypeEnum::TEACHER->value;

        if (!$this->user->getUser($user_id, $type)) {
            return $this->responseError(trans('api.error.user_not_permission'));
        }

        // Kiểm tra xem lớp có thuộc về giáo viên không
        $isTeachingClass = ClassSubjectTeacher::where('class_id', $classId)
        ->where('user_id', $user_id)
        ->exists();

        if (!$isTeachingClass) {
        return response()->json(['message' => 'Bạn không có quyền xem lớp này'], 403); 
        }

        $date = $request->input('date');
        if (!$date) {
            return response()->json(['message' => 'Yêu cầu cung cấp ngày cụ thể'], 400);
        }

        // Gọi tới repository để lấy chi tiết điểm danh
        $details = $this->TeacherRollCallHistoryRepository->getTeacherClassRollCallHistory($classId, $date);

        return response()->json($details);
    }







//tai khoan

public function showTeacherProfile(Request $request)
{
    // Lấy id người dùng đang đăng nhập
    $userId = Auth::user()->id;
    $type = AccessTypeEnum::TEACHER->value;

    // Kiểm tra quyền của giáo viên
    if (!$this->user->getUser($userId, $type)) {
        return $this->responseError(trans('api.error.user_not_permission'));
    }

    // Lấy thông tin giáo viên từ mô hình User, kèm theo môn học thông qua ClassSubjectTeacher
    $teacher = User::with(['classSubjectTeacher.subject' => function ($query) {
        $query->where('is_deleted', DeleteEnum::NOT_DELETE->value);
    }])
    ->where('id', $userId)
    ->where('access_type', $type) // Giáo viên
    ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
    ->first();

    if (!$teacher) {
        return response()->json(['message' => 'Không tìm thấy thông tin giáo viên'], 404);
    }

    // Lấy môn học đầu tiên
    $subject = $teacher->classSubjectTeacher->first()->subject ?? null;

    // Nếu là phương thức GET, trả về thông tin giáo viên
    if ($request->isMethod('get')) {
        return response()->json([
            'message' => 'Lấy thông tin giáo viên thành công',
            'status' => 'success',
            'teacher' => 'Giáo viên ' . $teacher->fullname,
            'data' => [
                'fullname' => $teacher->fullname,
                'email' => $teacher->email,
                'phone' => $teacher->phone,
                'dob' => $teacher->dob ? strtotime($teacher->dob) : null,
                'address' => $teacher->address,
                'access_type' => $teacher->access_type,
                'gender' => GenderEnum::from($teacher->gender)->value,
                'status' => StatusEnum::from($teacher->status)->value,
                'subject' => $subject ? $subject->name : 'Giáo viên chưa được gán môn dạy'
            ]
        ]);
    }

    // Nếu là phương thức POST (hoặc PUT), xử lý cập nhật thông tin
    if ($request->isMethod('post')) {
        $request->validate([
            'fullname' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string|max:15',
            'address' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
        ]);

        $teacher->update($request->only(['fullname', 'email', 'phone', 'address', 'dob']));

        return response()->json([
            'message' => 'Cập nhật thông tin giáo viên thành công',
            'status' => 'success'
        ]);
    }
}


}
            