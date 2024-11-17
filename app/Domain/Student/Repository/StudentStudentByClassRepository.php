<?php

namespace App\Domain\Student\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusClassStudentEnum;
use App\Common\Enums\StatusEnum;
use App\Domain\SchoolYear\Models\SchoolYear;
use App\Domain\Student\Requests\StudentUpdateRequest;
use App\Models\Classes;
use App\Models\Student as ModelsStudent;
use App\Models\StudentClassHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use SebastianBergmann\Type\TrueType;

class StudentStudentByClassRepository {


    public function handle(int $class_id = 0, string $keyword = '')
    {

        if ($class_id != 0) {

            $classCurrent = Classes::where('id', $class_id)->where('status', StatusEnum::ACTIVE->value)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->first();

            if (!$classCurrent) {
                return false;
            }

            $studentH = StudentClassHistory::where('class_id', $class_id)->where('end_date', null)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->get();

            $students = $studentH->map(function ($item) use ($keyword) {
                if (str_contains($item->student->fullname, $keyword)) {
                    return [
                        "studentId" => $item->student_id,
                        "studentName" => $item->student->fullname,
                        "studentDob" => strtotime($item->student->dob),
                        "className" => $item->class ? $item->class->name : ""
                    ];
                }
            })->toArray();

            return $students;

        } else {

            $studentH = StudentClassHistory::where('status', StatusClassStudentEnum::LEAVE->value)->where('end_date', null)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->get();


            $students = $studentH->map(function ($item) use ($keyword) {
                if (str_contains($item->student->fullname, $keyword)) {
                    return [
                        "studentId" => $item->student_id,
                        "studentName" => $item->student->fullname,
                        "studentDob" => strtotime($item->student->dob),
                        "className" => $item->class ? $item->class->name : ""
                    ];
                }
            })->toArray();

            return $students;

        }

    }



}
