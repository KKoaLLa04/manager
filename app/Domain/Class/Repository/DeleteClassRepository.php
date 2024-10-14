<?php

namespace App\Domain\Class\Repository;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\Class\Requests\CreateClassRequest;
use App\Domain\Class\Requests\DeleteClassRequest;
use App\Domain\Class\Requests\UpdateClassRequest;
use App\Models\Classes;
use App\Models\ClassSubjectTeacher;
use Illuminate\Support\Facades\Auth;

class DeleteClassRepository extends ClassRepository
{

    public function deleteClass(DeleteClassRequest $request)
    {
        return Classes::query()->where('id', $request->class_id)
            ->update(
                ['is_deleted'=> DeleteEnum::DELETED->value]
            );
    }

    public function updateClassTeacherSubject(int $classId)
    {
        return ClassSubjectTeacher::query()->where('class_id', $classId)
            ->update(['is_deleted' => DeleteEnum::DELETED->value]);
    }

}
