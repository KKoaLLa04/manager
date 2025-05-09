<?php

namespace App\Domain\Subject\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Common\Enums\SubjectPermanentEnum;
use App\Models\ClassSubject;
use App\Models\ClassSubjectTeacher;

class TeacherSubjectRepository
{

    public function getClassMainTeacher(int $classId, int $userId)
    {
        return ClassSubjectTeacher::query()
            ->where('class_id', $classId)
            ->where('user_id', $userId)
            ->whereNull('end_date')
            ->where('status', StatusEnum::ACTIVE->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('access_type', StatusTeacherEnum::MAIN_TEACHER->value)
            ->get();
    }

    public function getClassSubject(int $classId, int $userId, bool $mainTeacher = false)
    {
        if ($mainTeacher) {
            return ClassSubject::query()
                ->where('class_id', $classId)
                ->whereNotIn('subject_id', [SubjectPermanentEnum::CC->value, SubjectPermanentEnum::SHL->value])
                ->where('status', StatusEnum::ACTIVE->value)
                ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->with('subject')
                ->get();
        }else{
            $classSubjectTeacher = ClassSubjectTeacher::query()
                ->where('class_id', $classId)
                ->where('user_id', $userId)
                ->whereNull('end_date')
                ->where('status', StatusEnum::ACTIVE->value)
                ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->get();
            $classSubjectIds = $classSubjectTeacher->pluck('class_subject_id')->toArray();
            return ClassSubject::query()
                ->whereIn('id', $classSubjectIds)
                ->where('status', StatusEnum::ACTIVE->value)
                ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
                ->whereNotIn('subject_id', [SubjectPermanentEnum::CC->value, SubjectPermanentEnum::SHL->value])
                ->with('subject')
                ->get();
        }


    }
}
