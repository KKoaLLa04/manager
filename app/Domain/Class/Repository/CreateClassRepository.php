<?php

namespace App\Domain\Class\Repository;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\Class\Requests\CreateClassRequest;
use App\Domain\Subject\Models\Subject;
use App\Models\Classes;
use App\Models\ClassSubject;
use App\Models\ClassSubjectTeacher;
use Exception;
use Illuminate\Support\Facades\Auth;

class CreateClassRepository extends ClassRepository
{

    public function createClass(CreateClassRequest $request)
    {
        $dataInsert = [
            "name"            => isset($request->name) ? $request->name : "",
            "status"          => isset($request->status) ? $request->status : "",
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
        // $dataInsert = [
        //     "class_id"        => $classId,
        //     "user_id"         => $teacherId,
        //     "start_date"      => now(),
        //     "end_date"        => null,
        //     "status"          => StatusEnum::ACTIVE->value,
        //     "access_type"     => StatusTeacherEnum::MAIN_TEACHER->value,
        //     "is_deleted"      => DeleteEnum::NOT_DELETE->value,
        //     "created_user_id" => Auth::user()->id
        // ];
        //  ClassSubjectTeacher::query()->create($dataInsert);
         $this->assignDefaultSubjectsToClass($classId, $teacherId);

    }

    public function assignDefaultSubjectsToClass(int $classId, int $teacherId)
    {
        $defaultSubjects = Subject::query()
            ->whereIn('name', ['Chào cờ', 'Sinh hoạt'])
            ->get();

        if ($defaultSubjects->isEmpty()) {
            throw new Exception('Không tìm thấy môn học Chào cờ hoặc Sinh hoạt.');
        }

        foreach ($defaultSubjects as $subject) {
            $classSubject = ClassSubject::query()->create([
                "class_id"        => $classId,
                "subject_id"      => $subject->id,
                "status"          => StatusEnum::ACTIVE->value,
                "is_deleted"      => DeleteEnum::NOT_DELETE->value,
                "created_user_id" => Auth::user()->id
            ]);

            ClassSubjectTeacher::query()->create([
                "class_id"        => $classId,
                "class_subject_id" => $classSubject->id,
                "subject_id"      => $subject->id,
                "user_id"         => $teacherId,
                "start_date"      => now(),
                "end_date"        => null,
                "status"          => StatusEnum::ACTIVE->value,
                "access_type"     => StatusTeacherEnum::MAIN_TEACHER->value,
                "is_deleted"      => DeleteEnum::NOT_DELETE->value,
                "created_user_id" => Auth::user()->id
            ]);
        }

    }
}
