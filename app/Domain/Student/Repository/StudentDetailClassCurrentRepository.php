<?php

namespace App\Domain\Student\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Domain\SchoolYear\Models\SchoolYear;
use App\Domain\Student\Requests\StudentUpdateRequest;
use App\Models\Classes;
use App\Models\ClassSubjectTeacher;
use App\Models\Student as ModelsStudent;
use App\Models\StudentClassHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use SebastianBergmann\Type\TrueType;


class StudentDetailClassCurrentRepository {


    public function handle(int $class_id, string $keyword = '')
    {

        $classCurrent = Classes::where('id', $class_id)->where('status', StatusEnum::ACTIVE->value)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->first();

        if (!$classCurrent) {
            return false;
        }

        $ClassST = ClassSubjectTeacher::where('class_id', $class_id)->where('status', StatusEnum::ACTIVE->value)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->first();

        $teacherMain = null;

        if ($ClassST) {
            $teacherMain = User::find($ClassST->user_id);
        }

        $studentH = StudentClassHistory::where('class_id', $class_id)->where('end_date', null)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->get();

        $students = $studentH->map(function ($item) use ($classCurrent, $keyword) {
            if (str_contains($item->student->fullname, $keyword)) {
                return [
                    "studentId" => $item->student_id,
                    "studentName" => $item->student->fullname,
                    "studentDob" => strtotime($item->student->dob),
                    "className" => $classCurrent->name
                ];
            }
        })->toArray();

        $students = array_filter($students);

        return [
            "className" => $classCurrent->name,
            "classId" => $classCurrent->id,
            "classTeacherMain" => $teacherMain ? $teacherMain->fullname : "",
            "classSumStudent" => $studentH->count(),
            "students" => $students
        ];

    }



}
