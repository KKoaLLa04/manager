<?php

namespace App\Domain\LeaveRequest\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\LeaveRequestEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\LeaveRequest\Models\LeaveRequest;
use App\Domain\LeaveRequest\Requests\LeaveRequestRequest;
use App\Jobs\CancelNotification;
use App\Models\ClassSubjectTeacher;

class LeaveRequestTeacherResponsitory
{
    public function getRequest($keyword = null, $pageIndex = 1, $pageSize = 10)
    {
        // Lấy danh sách lớp mà giáo viên chủ nhiệm
        $teacherClasses = ClassSubjectTeacher::where('user_id', auth()->id())
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('access_type', StatusTeacherEnum::MAIN_TEACHER->value)
            ->pluck('class_id');

        // Lấy danh sách lớp mà giáo viên giảng dạy
        $teacherSubjects = ClassSubjectTeacher::where('user_id', auth()->id())
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('access_type', StatusTeacherEnum::TEACHER->value)
            ->pluck('class_id');

        // Hợp nhất danh sách lớp chủ nhiệm và lớp giảng dạy
        $allTeacherClasses = $teacherClasses->merge($teacherSubjects);

        // Truy vấn yêu cầu nghỉ học chỉ thuộc các lớp mà giáo viên dạy hoặc chủ nhiệm
        $query = LeaveRequest::with(['parent', 'student', 'class', 'processedBy'])
            ->whereHas('class', function ($q) use ($allTeacherClasses) {
                $q->whereIn('id', $allTeacherClasses);
            })
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value);

        if ($keyword) {
            $query->where(function ($subQuery) use ($keyword) {
                $subQuery->where('code', 'LIKE', '%' . $keyword . '%')
                    ->orWhereHas('parent', function ($q) use ($keyword) {
                        $q->where('fullname', 'LIKE', '%' . $keyword . '%');
                    })
                    ->orWhereHas('student', function ($q) use ($keyword) {
                        $q->where('fullname', 'LIKE', '%' . $keyword . '%');
                    })
                    ->orWhereHas('class', function ($q) use ($keyword) {
                        $q->where('name', 'LIKE', '%' . $keyword . '%');
                    });
            });
        }

        $leaveRequests = $query->paginate($pageSize, ['*'], 'page', $pageIndex);

        $mappedData = $leaveRequests->getCollection()->map(function ($item) use ($teacherClasses) {
            // Kiểm tra nếu giáo viên là chủ nhiệm của lớp
            $isMainTeacher = $teacherClasses->contains($item->class->id);

            return [
                'id' => $item->id,
                'code' => $item->code,
                'title' => $item->title,
                'note' => $item->note,
                'leaveDate' => $item->leave_date,
                'returnDate' => $item->return_date,
                'time' => $item->time,
                'status' => $item->status,
                'processedBy' => $item->processedBy->fullname ?? 'N/A',
                'parentName' => $item->parent->fullname ?? 'N/A',
                'studentName' => $item->student->fullname ?? 'N/A',
                'className' => $item->class->name ?? 'N/A',
                'refuseNote' => $item->refuse_note,
            ];
        });

        return [
            'data' => $mappedData,
            'total' => $leaveRequests->total(),
            'current_page' => $leaveRequests->currentPage(),
            'last_page' => $leaveRequests->lastPage(),
            'per_page' => $leaveRequests->perPage(),
        ];
    }




    public function getOneRequest($id)
    {
        // Lấy danh sách lớp mà giáo viên chủ nhiệm
        $teacherClasses = ClassSubjectTeacher::where('user_id', auth()->id())
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('access_type', StatusTeacherEnum::MAIN_TEACHER->value)
            ->pluck('class_id');

        // Lấy danh sách lớp mà giáo viên giảng dạy
        $teacherSubjects = ClassSubjectTeacher::where('user_id', auth()->id())
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('access_type', StatusTeacherEnum::TEACHER->value)
            ->pluck('class_id');

        // Hợp nhất danh sách lớp
        $allTeacherClasses = $teacherClasses->merge($teacherSubjects);

        // Lấy đơn yêu cầu với kiểm tra lớp thuộc quyền của giáo viên
        $one = LeaveRequest::with(['parent', 'student', 'class', 'processedBy'])
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->whereHas('class', function ($q) use ($allTeacherClasses) {
                $q->whereIn('id', $allTeacherClasses);
            })
            ->find($id);

        // Kiểm tra nếu không tìm thấy đơn
        if (!$one) {
            return response()->json(['message' => 'Không tìm thấy đơn hoặc bạn không có quyền truy cập'], 404);
        }

        // Kiểm tra nếu giáo viên là chủ nhiệm
        $isMainTeacher = $teacherClasses->contains($one->class->id);

        // Trả về dữ liệu đã được ánh xạ
        return response()->json([
            'id' => $one->id,
            'title' => $one->title,
            'processedBy' => auth()->user()->fullname ?? 'N/A',
            'studentName' => $one->student->fullname ?? 'N/A',
            'refuseNote' => $one->refuse_note,
            'className' => $one->class->name ?? 'N/A',
        ]);
    }





    public function accept($id)
    {
        // Lấy thông tin đơn xin nghỉ học
        $one = LeaveRequest::with('class')
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->find($id);

        if (!$one) {
            return response()->json(['message' => 'Không tìm thấy đơn'], 404);
        }

        // Lấy danh sách lớp mà giáo viên là chủ nhiệm
        $teacherClasses = ClassSubjectTeacher::where('user_id', auth()->id())
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('access_type', StatusTeacherEnum::MAIN_TEACHER->value)
            ->pluck('class_id');

        // Kiểm tra nếu giáo viên không phải là chủ nhiệm của lớp
        if (!$teacherClasses) {
            return response()->json(['message' => 'Bạn không có quyền chấp nhận đơn này'], 403);
        }

        // Chấp nhận đơn xin nghỉ học
        $one->status = LeaveRequestEnum::ACCEPT->value;
        $one->processed_by = auth()->id();
        $one->save();

        // Gửi thông báo hủy
        CancelNotification::dispatch($one);

        return response()->json(['message' => 'Đơn đã được chấp nhận', 'data' => $one], 200);
    }


    public function reject($id, $data)
    {
        $one = LeaveRequest::with('class')
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->find($id);

        if (!$one) {
            return response()->json(['message' => 'Không tìm thấy đơn'], 404);
        }

        $teacherClasses = ClassSubjectTeacher::where('user_id', auth()->id())
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('access_type', StatusTeacherEnum::MAIN_TEACHER->value)
            ->pluck('class_id');

        if (!$teacherClasses->contains($one->class->id)) {
            return response()->json(['message' => 'Bạn không có quyền hủy đơn này'], 403);
        }

        
        $one->fill($data);
        $one->processed_by = auth()->id(); 
        $one->save();

       
        if ($one->status == LeaveRequestEnum::REJECT->value) {
            CancelNotification::dispatch($one); 
        }

        return response()->json(['message' => 'Đơn đã được hủy thành công.'], 200);
    }
}
