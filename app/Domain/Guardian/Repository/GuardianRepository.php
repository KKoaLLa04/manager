<?php

namespace App\Domain\Guardian\Repository;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Domain\Guardian\Models\Guardian;
use App\Models\Student;
use App\Models\User;
use Exception;

class GuardianRepository
{
    public function __construct() {}

    public function getGuardian($keyword = null, $pageIndex = 1, $pageSize = 10)
    {
        $query = Guardian::where('access_type', AccessTypeEnum::GUARDIAN->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->with(['students.classHistories']);

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('fullname', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('phone', 'LIKE', '%' . $keyword . '%');
            });
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
                'username' => $guardian->username,
                'dob' => $guardian->dob,
                'address' => $guardian->address,
                'password' => $guardian->password,
                'confirm_password' => $guardian->confirm_password,
                'studentsInfo' => $guardian->students->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'code' => $student->code,
                        'fullname' => $student->fullname,
                        'email' => $student->email,
                        'dob' => strtotime($student->dob),
                        'gender' => $student->gender,
                        'phone' => $student->phone,
                        'academicYear' => $student->classHistories->isNotEmpty() &&
                        $student->classHistories->first()->class &&
                        $student->classHistories->first()->class->academicYear
                        ? $student->classHistories->first()->class->academicYear->name
                        : null,

                        'username' => $student->username,
                    ];
                }),
            ];
        });

        return [
            'data' => $mappedData,
            'total' => $paginatedResult->total(),
        ];
    }



    public function addGuardian(array $data)
    {
        return Guardian::create($data);
    }

    public function getOneGuardian(int $id)
    {

        $one = Guardian::with('students')
            ->where('access_type', AccessTypeEnum::GUARDIAN->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->find($id);


        if (!$one) {
            return [
                'msg' => 'Phụ huynh không tồn tại',
                'data' => null,
            ];
        }


        // if ($one->status == StatusEnum::UN_ACTIVE->value) {
        //     return [
        //         'msg' => 'Phụ huynh đang bị khoá',
        //         'data' => null,
        //     ];
        // }


        $guardianData = [
            'id' => $one->id,
            'fullname' => $one->fullname,
            'phone' => $one->phone,
            'email' => $one->email,
            'code' => $one->code,
            'dob' => strtotime($one->dob),
            'status' => $one->status,
            'address' => $one->address,
            'usernames' => $one->username,
            'students' => $one->students->map(function ($student) {
                return [
                    'id' => $student->id,
                    'student_code' => $student->student_code,
                    'fullname' => $student->fullname,
                    'email' => $student->email,
                    'gender' => $student->gender,
                    'dob' => strtotime($student->dob),
                    'phone' => $student->phone,
                    'academicYear' => $student->classHistories->first()->class->academicYear->name,
                    'username' => $student->username,
                ];
            }),
        ];

        return [
            'data' => $guardianData,
        ];
    }



    public function getStudent($keyword = null, $pageIndex = 1, $pageSize = 10)
    {
        // Truy vấn dữ liệu từ bảng student và liên kết với classHistories
        $query = Student::where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('status', StatusEnum::ACTIVE->value);

        // Tìm kiếm theo từ khóa nếu có
        if ($keyword) {
            $query->where('fullname', 'LIKE', '%' . $keyword . '%')
                ->orWhere('student_code', 'LIKE', '%' . $keyword . '%');
        }

        // Lấy dữ liệu sinh viên phân trang
        $students = $query->paginate($pageSize, ['*'], 'page', $pageIndex);

        $data = $students->map(function ($student) {
            return [
                'id' => $student->id,
                'student_code' => $student->student_code,
                'fullname' => $student->fullname,
                'email' => $student->email,
                'phone' => $student->phone,
                'gender' => $student->gender,
                'dob' => strtotime($student->dob),
                'username' => $student->username,
                'academicYear' => $student->classHistories->first()->class->academicYear->name
            ];
        });




        // Trả về kết quả dạng API
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

        $guardian = Guardian::where('access_type', AccessTypeEnum::GUARDIAN->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->find($id);


        if (!$guardian) {
            return response()->json([
                'message' => 'Phụ huynh không tồn tại'
            ], 404);
        }


        if ($guardian->is_deleted == DeleteEnum::DELETED->value) {
            return response()->json([
                'message' => 'Phụ huynh đang bị xóa'
            ], 403);
        }


        $guardian->fill($data);
        $guardian->save();


        return [
            'data' => []
        ];
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
    public function getUser($userId, $accessType)
    {
        return User::where('id', $userId)
            ->where('access_type', $accessType)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->first();
    }
}
