<?php

namespace App\Domain\Guardian\Repository;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Domain\Guardian\Models\Guardian;
use App\Models\Student;
use Exception;

class GuardianRepository
{
    public function __construct() {}

    public function getGuardian($keyword = null, $pageIndex = 1, $pageSize = 10)
    {
        $query = Guardian::where('access_type', AccessTypeEnum::GUARDIAN->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->withCount('students');

        // Filter by keyword if provided
        if ($keyword) {
            $query->where('fullname', 'LIKE', '%' . $keyword . '%')
                ->orWhere('phone', 'LIKE', '%' . $keyword . '%');
        }


        $paginatedResult = $query->paginate($pageSize);


        $mappedData = $paginatedResult->getCollection()->map(function ($guardian) {
            return [
                'id' => $guardian->id,
                'fullname' => $guardian->fullname,
                'phone' => $guardian->phone,
                'email' => $guardian->email,
                'code' => $guardian->code,
                'status' => $guardian->status,
                'gender' => $guardian->gender,
                'career' => $guardian->career,
                'students_count' => $guardian->students_count
            ];
        });


        return [
            'data' => $mappedData,
            'total' => $paginatedResult->total()
        ];
    }




    public function addGuardian(array $data)
    {
        return Guardian::create($data);
    }

    public function getOneGuardian(int $id)
    {
        // Lấy dữ liệu phụ huynh cùng với học sinh
        $one = Guardian::with('students')
            ->where('access_type', AccessTypeEnum::GUARDIAN->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->find($id);

        // Kiểm tra phụ huynh có tồn tại không
        if (!$one) {
            return response()->json([
                'message' => 'Phụ huynh không tồn tại'
            ], 404);
        }

        // Kiểm tra trạng thái phụ huynh
        if ($one->status == StatusEnum::UN_ACTIVE->value) {
            return response()->json([
                'message' => 'Phụ huynh đang bị khoá'
            ], 403);
        }

        // Map lại dữ liệu phụ huynh
        $guardianData = [
            'id' => $one->id,
            'fullname' => $one->fullname,
            'phone' => $one->phone,
            'email' => $one->email,
            'code' => $one->code,
            'dob' => $one->dob,
            'status' => $one->status,
            'address' => $one->address,
            'students' => $one->students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'student_code' => $student->student_code,
                    'fullname' => $student->fullname,
                    'email' => $student->email,
                    'gender' => $student->gender,
                    'dob' => $student->dob,
                    'phone' => $student->phone,
                    'academicYear' => $student->academic,
                    'username' => $student->username
                ];
            })
        ];


        return response()->json([
            'data' => $guardianData,
        ]);
    }


    public function getStudent($keyword = null, $pageIndex = 1, $pageSize = 10)
{
    // Tạo query để lấy danh sách học sinh
    $query = Student::where('is_deleted', DeleteEnum::NOT_DELETE->value)
        ->where('status', StatusEnum::ACTIVE->value);

    // Tìm kiếm theo từ khóa nếu có
    if ($keyword) {
        $query->where('fullname', 'LIKE', '%' . $keyword . '%')
              ->orWhere('student_code', 'LIKE', '%' . $keyword . '%');
    }

    // Paginate với số lượng học sinh mỗi trang
    $students = $query->paginate($pageSize, ['*'], 'page', $pageIndex);

    // Sử dụng map để lược bỏ các trường không cần thiết
    $data = $students->map(function ($student) {
        return [
            'id' => $student->id,
            'student_code' => $student->student_code,
            'fullname' => $student->fullname,
            'email' => $student->email,
            'phone' => $student->phone,
            'gender' => $student->gender,
            'dob' => $student->dob,
            'username' => $student->username,
            'academicYear' => $student->academicYear, // Nếu cần, hoặc có thể bỏ
        ];
    });

    // Trả về dữ liệu bao gồm cả total và bỏ lớp lồng "data"
    return [
        'data' => $data,
        'total' => $students->total()
    ];
}





    public function updateGuardian(int $id, array $data)
    {
        $one = Guardian::where('access_type', AccessTypeEnum::GUARDIAN->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->find($id);

        if (!$one) {
            return response()->json([
                'message' => 'Phụ huynh không tồn tại'
            ], 404);
        }

        if ($one->is_deleted == DeleteEnum::DELETED->value) {
            return response()->json([
                'message' => 'Phụ huynh đang bị xóa'
            ], 403);
        }

        $one->fill($data);
        $one->save();
        return $one;
    }

    public function lockGuardian(int $id)
    {
        $one = Guardian::where('access_type', AccessTypeEnum::GUARDIAN->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->find($id);


        if (!$one) {
            return response()->json([
                'message' => 'Phụ huynh không tồn tại'
            ], 404);
        }

        if ($one->is_deleted == DeleteEnum::DELETED->value) {
            return response()->json([
                'message' => 'Phụ huynh đang bị xóa'
            ], 403);
        }

        if ($one->status == 1) {
            $one->status = 0;
            $one->save();
            return $one;
        }
        return null;
    }

    public function unlockGuardian(int $id)
    {
        $one = Guardian::where('access_type', AccessTypeEnum::GUARDIAN->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->find($id);

        if (!$one) {
            return response()->json([
                'message' => 'Phụ huynh không tồn tại'
            ], 404);
        }

        if ($one->is_deleted == DeleteEnum::DELETED->value) {
            return response()->json([
                'message' => 'Phụ huynh đang bị xóa'
            ], 403);
        }

        if ($one->status == 0) {
            $one->status = 1;
            $one->save();
            return $one;
        }
        return null;
    }

    public function changePassword(int $id, array $data)
    {
        $one = Guardian::where('access_type', AccessTypeEnum::GUARDIAN->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->find($id);

        if (!$one) {
            return response()->json([
                'message' => 'Phụ huynh không tồn tại'
            ], 404);
        }

        if ($one->is_deleted == DeleteEnum::DELETED->value) {
            return response()->json([
                'message' => 'Phụ huynh đang bị xóa'
            ], 403);
        }

        $one->fill($data);
        $one->save();
        return $one;
    }

    public function assignStudent(int $guardianId, array $studentIds, int $createdUserId, int $deletedUser = 0)
    {

        $parent = Guardian::where('access_type', AccessTypeEnum::GUARDIAN->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->find($guardianId);

        if ($parent) {
            $assignedStudents = [];
            $alreadyAssigned = [];


            foreach ($studentIds as $studentId) {

                if (!$parent->students()->where('student_id', $studentId)->exists()) {

                    $parent->students()->attach($studentId, ['created_user_id' => $createdUserId, 'is_deleted' => $deletedUser]);


                    $assignedStudents[] = Student::find($studentId);
                } else {
                    throw new Exception('Học sinh đã có phụ huynh!');
                }
            }


            return [
                'guardian' => $parent,
                'assigned_students' => $assignedStudents,
                'already_assigned' => $alreadyAssigned
            ];
        } else {
            throw new Exception('Phụ huynh không tồn tại.');
        }
    }

    public function unassignStudent(int $guardianId, array $studentIds)
    {
        $parent = Guardian::where('access_type', AccessTypeEnum::GUARDIAN->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->find($guardianId);

        if ($parent) {
            $parent->students()->detach($studentIds);
            return true;
        } else {
            throw new Exception('Phụ huynh không tồn tại.');
        }
    }
}
