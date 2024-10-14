<?php

namespace App\Domain\Class\Repository;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\Class\Requests\CreateClassRequest;
use App\Models\Classes;
use App\Models\ClassSubjectTeacher;
use Illuminate\Support\Facades\Auth;

class CreateClassRepository extends ClassRepository
{

    public function createClass(CreateClassRequest $request)
    {
        $dataInsert = [
            "name"            => isset($request->name) ? $request->name : "",
            "code"            => now()->timestamp,
            "school_year_id"  => isset($request->school_year_id) ? $request->school_year_id : null,
            "academic_year_id"     => isset($request->academic_id) ? $request->academic_id : null,
            "grade_id"        => isset($request->grade_id) ? $request->grade_id : null,
            "created_user_id" => Auth::user()->id
        ];

        return Classes::query()->create($dataInsert);
    }

    public function createClassTeacherSubject(int $classId, int $teacherId)
    {
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
