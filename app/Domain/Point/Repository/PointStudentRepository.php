<?php

namespace App\Domain\Point\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Domain\Subject\Models\Subject;
use App\Models\Classes;
use App\Models\Exam;
use App\Models\ExamPeriod;
use App\Models\PointStudent;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PointStudentRepository
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

    public function getPointStudent(int $classId, array $examPeriodIds, array $studentIds, int $subjectId): Collection
    {
        return PointStudent::query()
            ->where('class_id', $classId)
            ->whereIn('exam_period_id', $examPeriodIds)
            ->whereIn('student_id', $studentIds)
            ->where('subject_id', $subjectId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get();
    }

    public function storePointStudent(int $classId, int $subjectId, array $data): void
    {
        $dataConverts = [];
        $studentIds   = [];
        foreach ($data as $item) {
            foreach ($item['students'] as $student) {
                $studentIds[]   = $student['studentId'];
                $dataConverts[] = [
                    "student_id"     => $student['studentId'],
                    "exam_period_id" => $item['examPeriodId'],
                    "subject_id"     => $subjectId,
                    "class_id"       => $classId,
                    "point"          => $student['point'],
                    "created_by"     => Auth::id(),
                    "created_at"     => now(),
                    "updated_at"     => now(),
                ];
            }
        }

        $pointStudents = $this->getPointStudentByStudentIdsAndSubjectIdAndClassId($studentIds, $subjectId, $classId);
        $dataInsert    = [];
        $dataUpdate    = [];
        foreach ($dataConverts as $data) {
            $pointStudent = $pointStudents
                ->where('student_id', $data['student_id'])
                ->where('exam_period_id', $data['exam_period_id'])
                ->where('subject_id', $data['subject_id'])
                ->where('class_id', $data['class_id'])->first();
            if (is_null($pointStudent)) {
                $dataInsert[] = [
                    "student_id"     => $data['student_id'],
                    "exam_period_id" => $data['exam_period_id'],
                    "subject_id"     => $data['subject_id'],
                    "class_id"       => $data['class_id'],
                    "point"          => $data['point'],
                    "note"           => $data['note'] ?? "",
                    "created_by"     => Auth::id(),
                    "created_at"     => now(),
                    "updated_at"     => now(),
                ];
            } else {
                $dataUpdate[] = [
                    "id"             => $pointStudent->id,
                    "student_id"     => $data['student_id'],
                    "exam_period_id" => $data['exam_period_id'],
                    "subject_id"     => $data['subject_id'],
                    "class_id"       => $data['class_id'],
                    "point"          => $data['point'],
                    "note"           => $data['note'] ?? "",
                    "updated_by"     => Auth::id(),
                    "created_at"     => now(),
                    "updated_at"     => now(),
                ];
            }
        }

        if (!empty($dataInsert)) {
            PointStudent::query()->insert($dataInsert);
        }
        if (!empty($dataUpdate)) {
            batch()->update(new  PointStudent, $dataUpdate, 'id');
        }
    }

    public function getPointStudentByStudentIdsAndSubjectIdAndClassId(
        array $studentIds,
        int   $subjectId,
        int   $classId
    ): Collection {
        return PointStudent::query()
            ->whereIn('student_id', $studentIds)
            ->where('subject_id', $subjectId)
            ->where('class_id', $classId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get();
    }


    public function transform(
        Classes    $class,
        Subject    $subject,
        Collection $students,
        Collection $pointStudents,
        Collection $examPeriods,
        Collection $exams
    ): array {
        $data = $students->map(function ($item) use ($pointStudents) {
            $pointStudents = $pointStudents->where('student_id', $item->id);
            return [
                "id"     => $item->id,
                "name"   => $item->fullname,
                "code"   => $item->student_code,
                "note"   => $item->note,
                "dob"    => Carbon::parse($item->dob)->timestamp,
                "points" => $pointStudents->map(function ($item) {
                    return [
                        "id"             => $item->id,
                        "exam_period_id" => $item->exam_period_id,
                        "point"          => $item->point ?? 0,
                        "note"           => $item->note ?? "",
                    ];
                })->values()->toArray(),
            ];
        })->toArray();
//        $data = $students->map(function ($item) use ($pointStudents, $examPeriods, $exams): array {
//            return [
//                "exams" => $exams->map(function ($item) use (
//                    $pointStudents,
//                    $examPeriods,
//                ) {
//                    $examPeriod = $examPeriods->get($item->id);
//                    return [
//                        "examId"      => $item->id,
//                        "examName"    => $item->name,
//                        "examPeriods" => !isset($examPeriod) ? [] : $examPeriod->map(function ($item) use (
//                            $pointStudents,
//                        ) {
//                            $pointStudent = $pointStudents->where('exam_period_id',
//                                $item->id)->first();
//                            return [
//                                "examPeriodId"   => $item->id,
//                                "examPeriodDate" => $item->date,
//                                "examPeriodName" => $item->name,
//                                "pointStudentId" => $pointStudent->id,
//                                "point"          => isset($pointStudent) ? $pointStudent->point : "",
//                                "note"           => isset($pointStudent) ? $pointStudent->note : "",
//                            ];
//                        })->toArray()
//                    ];
//                })->toArray()
//            ];
//        });
        $struct = $exams->map(function ($item) use ($examPeriods) {
            $examPeriod = $examPeriods->get($item->id);
            return [
                "examId"      => $item->id,
                "examName"    => $item->name,
                "examPeriods" => !isset($examPeriod) ? [] : $examPeriod->map(function ($item) {
                    return [
                        "examPeriodId"   => $item->id,
                        "examPeriodDate" => $item->date,
                        "examPeriodName" => $item->name,
                    ];
                })->toArray()
            ];
        })->toArray();
        return [
            "class"   => [
                "id"   => $class->id,
                "name" => $class->name,
            ],
            "subject" => [
                "id"   => $subject->id,
                "name" => $subject->name,
            ],
            "struct"  => $struct,
            "data"    => $data,
        ];
    }

    public function getClassById(int $classId): Classes
    {
        return Classes::query()->where("id", $classId)->first();
    }

    public function getSubjectById(int $subjectId): Subject
    {
        return Subject::query()->where("id", $subjectId)->first();
    }
}
