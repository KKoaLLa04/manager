<?php

namespace App\Domain\Class\Repository;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\Class\Requests\CreateClassRequest;
use App\Domain\Class\Requests\UpdateClassRequest;
use App\Models\Classes;
use App\Models\ClassSubjectTeacher;
use Illuminate\Support\Facades\Auth;

class UpdateClassRepository extends ClassRepository
{

    public function UpdateClass(UpdateClassRequest $request)
    {
        $dataUpdate = [
            "name"            => isset($request->name) ? $request->name : "",
            "code"            => now()->timestamp,
            "grade_id"        => isset($request->grade_id) ? $request->grade_id : null,
            "modified_user_id" => Auth::user()->id
        ];

         Classes::query()->where('id', $request->class_id)->update($dataUpdate);
    }

    public function createClassTeacherSubject(int $classId, int $teacherId)
    {
        $checkTeacherExits = $this->checkTeacherExistInClass($classId, $teacherId);
        if(!$checkTeacherExits){
            ClassSubjectTeacher::query()->where('class_id', $classId)
                ->update(
                    [
                        'end_date' => now(),
                        'status' => StatusEnum::UN_ACTIVE->value,
                        "modified_user_id" => Auth::user()->id
                    ]
                );
            $dataInsert = [
                "class_id"        => $classId,
                "user_id"         => $teacherId,
                "start_date"      => now(),
                "end_date"        => null,
                "status"          => StatusEnum::ACTIVE->value,
                "access_type"     => StatusTeacherEnum::MAIN_TEACHER->value,
                "is_deleted"      => DeleteEnum::NOT_DELETE->value,
                "created_user_id" => Auth::user()->id
            ];
            return ClassSubjectTeacher::query()->create($dataInsert);
        }

    }
    private function checkTeacherExistInClass(int $classId, int $teacherId)
    {
        return ClassSubjectTeacher::query()->where('class_id', $classId)
            ->where('user_id', $teacherId)
            ->where('status', StatusEnum::ACTIVE->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('access_type', StatusTeacherEnum::MAIN_TEACHER->value)
            ->exists();
    }
}
