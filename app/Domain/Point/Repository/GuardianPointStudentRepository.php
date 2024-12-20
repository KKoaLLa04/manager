<?php

namespace App\Domain\Point\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Domain\Subject\Models\Subject;
use App\Models\Classes;
use App\Models\Exam;
use App\Models\ExamPeriod;
use App\Models\PointStudent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class GuardianPointStudentRepository
{

    public function __construct()
    {
    }

    public function getExamBySchoolYearId(int $schoolYearId): Collection
    {
        return Exam::query()->where('school_year_id', $schoolYearId)
            ->where('status', StatusEnum::ACTIVE->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get();
    }

    public function getExamPeriodByIds(array $examIds): Collection
    {
        return ExamPeriod::query()
            ->whereIn('id', $examIds)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get();
    }

    public function getPointStudent(int $studentId, array $examPeriodIds): Collection
    {
        return PointStudent::query()
            ->where('student_id', $studentId)
            ->whereIn('exam_period_id', $examPeriodIds)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get();
    }

    public function getClassesByIds(array $classIds): Collection
    {
        return Classes::query()->whereIn('id', $classIds)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get();
    }

    public function getSubjectsByIds(array $subjectIds): Collection
    {
        return Subject::query()->whereIn('id', $subjectIds)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get();
    }

    public function transform(
        Collection $classes,
        Collection $subjects,
        Collection $pointStudentsGroup,
        Collection $examPeriods,
        Collection $exams
    ): array {
//        $data = $classes->map(function ($item) use (
//            $subjects,
//            $pointStudentsGroup,
//            $examPeriods,
//            $exams
//        ) {
//            $classId       = $item->id;
//            $pointStudents = $pointStudentsGroup->get($classId);
//            return [
//                "id"       => $classId,
//                "name"     => $item->name,
//                "subjects" => $subjects->map(function ($item) use (
//                    $pointStudents,
//                    $examPeriods,
//                    $exams
//                ) {
//                    $subjectId              = $item->id;
//                    $pointStudentsOfSubject = $pointStudents->where('subject_id', $subjectId);
//                    return [
//                        "subjectId"   => $item->id,
//                        "subjectName" => $item->name,
//                        "exams"       => $exams->map(function ($item) use (
//                            $pointStudentsOfSubject,
//                            $examPeriods,
//                            $subjectId
//                        ) {
//                            $examPeriod = $examPeriods->get($item->id);
//                            return [
//                                "examId"      => $item->id,
//                                "examName"    => $item->name,
//                                "examPeriods" => !isset($examPeriod) ? [] : $examPeriod->map(function ($item) use (
//                                    $pointStudentsOfSubject,
//                                    $subjectId
//                                ) {
//                                    $pointStudent = $pointStudentsOfSubject->where('exam_period_id',
//                                        $item->id)->first();
//                                    return [
//                                        "examPeriodDate" => $item->date,
//                                        "examPeriodName" => $item->name,
//                                        "point"          => isset($pointStudent) ? $pointStudent->point : "",
//                                        "note"           => isset($pointStudent) ? $pointStudent->note ?? "" : "",
//                                    ];
//                                })->toArray()
//                            ];
//                        })->toArray()
//                    ];
//                })->toArray()
//            ];
//        })->toArray();
        $data = $classes->map(function ($item) use (
            $subjects,
            $pointStudentsGroup,
        ) {
            $classId       = $item->id;
            $pointStudents = $pointStudentsGroup->get($classId);
            return [
                "id"       => $classId,
                "name"     => $item->name,
                "subjects" => $subjects->map(function ($item) use (
                    $pointStudents,
                ) {
                    $subjectId              = $item->id;
                    $pointStudentsOfSubject = $pointStudents->where('subject_id', $subjectId);
                    return [
                        "subjectId"   => $item->id,
                        "subjectName" => $item->name,
                        "points"       => $pointStudentsOfSubject->map(function ($item) {
                            return [
                                "id"             => $item->id,
                                "exam_period_id" => $item->exam_period_id,
                                "point"          => $item->point ?? 0,
                                "note"           => $item->note ?? "",
                            ];
                        })->values()->toArray(),
                    ];
                })->toArray()
            ];
        })->toArray();


        $struct = $exams->map(function ($item) use ($examPeriods) {
            $examPeriod = $examPeriods->get($item->id);
            return [
                "examId"      => $item->id,
                "examName"    => $item->name,
                "examPeriods" => !isset($examPeriod) ? [] : $examPeriod->map(function ($item) {
                    return [
                        "examPeriodDate" => $item->date,
                        "examPeriodName" => $item->name,
                    ];
                })->toArray()
            ];
        })->toArray();
        return [
            "struct" => $struct,
            "data"   => $data,
        ];
    }


}
