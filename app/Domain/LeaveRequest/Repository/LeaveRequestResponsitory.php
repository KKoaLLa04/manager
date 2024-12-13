<?php

namespace App\Domain\LeaveRequest\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\LeaveRequestEnum;
use App\Domain\LeaveRequest\Models\LeaveRequest;
use App\Domain\LeaveRequest\Requests\LeaveRequestRequest;

class LeaveRequestResponsitory
{
    public function getRequest($keyword = null, $pageIndex = 1, $pageSize = 10)
    {
        $query = LeaveRequest::with(['parent', 'student', 'class', 'processedBy'])
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

        $mappedData = $leaveRequests->getCollection()->map(function ($item) {
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
        // Lấy đơn yêu cầu theo ID, với các quan hệ đã eager load
        $one = LeaveRequest::with(['parent', 'student', 'class', 'processedBy'])
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->find($id); // Dùng find() để lấy một bản ghi duy nhất
    
        // Kiểm tra nếu không tìm thấy đơn
        if (!$one) {
            return response()->json(['message' => 'Không tìm thấy đơn'], 404);
        }
    
        // Trả về dữ liệu đã được ánh xạ
        return response()->json([
            'id' => $one->id,
            'title' => $one->title,
            'processedBy' =>  auth()->user()->fullname ?? 'N/A',
            'studentName' => $one->student->fullname ?? 'N/A',
            'refuseNote' => $one->refuse_note,
        ]);
    }
    



    public function accept($id)
    {
        $one = LeaveRequest::where('is_deleted', DeleteEnum::NOT_DELETE->value)->find($id);

        if (!$one) {
            return response()->json(['message' => 'Không tìm thấy đơn'], 404);
        }

        $one->status = LeaveRequestEnum::ACCEPT->value;
        $one->processed_by = auth()->id();
        $one->save();

        return $one;
    }

    public function reject($id, $data)
    {
        // Tìm đơn xin dựa vào ID
        $one = LeaveRequest::where('is_deleted', DeleteEnum::NOT_DELETE->value)->find($id);


        if (!$one) {
            return response()->json(['message' => 'Không tìm thấy đơn'], 404);
        }


        $one->fill($data);
        $one->save();

        return $one;
    }
}
