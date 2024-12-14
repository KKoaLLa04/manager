<?php

namespace App\Domain\LeaveRequestGuardian\Repository;

use App\Common\Enums\DeleteEnum;
use App\Domain\LeaveRequestGuardian\Models\LeaveRequestGuardian;
use App\Domain\LeaveRequestGuardian\Requests\LeaveRequestGuardianRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaveRequestGuardianRepository
{

    public function getLeaveRequestByParent($parentUserId, $pageSize = null, $pageIndex = null)
    {
        return LeaveRequestGuardian::select(
            'leave_request.id',
            'leave_request.code',
            'leave_request.title',
            'students.fullname as student_name',
            'classes.name as class_name',
            'leave_request.note',
             DB::raw("CONCAT(leave_request.leave_date, ' ', leave_request.time) as leave_date_time"),
             'leave_request.status',
        )
        ->join('students', 'leave_request.student_id', '=', 'students.id')
        ->join('classes', 'leave_request.class_id', '=', 'classes.id')
        ->where('leave_request.parent_user_id', $parentUserId) // Chỉ lấy đơn của con phụ huynh này
        ->where('leave_request.is_deleted', DeleteEnum::NOT_DELETE->value)
        ->paginate($pageSize, ['*'], 'page', $pageIndex);
    }

    public function cancelLeaveRequest(int $leaveRequestId): bool
    {
        $leaveRequest = LeaveRequestGuardian::find($leaveRequestId);

        if ($leaveRequest) {
            $leaveRequest->is_deleted = DeleteEnum::DELETED->value;

            return $leaveRequest->save();
        }

        // Trả về false nếu không tìm thấy đơn
        return false;
    }



}
