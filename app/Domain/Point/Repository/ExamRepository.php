<?php

namespace App\Domain\Point\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Domain\Subject\Models\Subject;
use App\Models\ClassSubject;
use App\Models\ClassSubjectTeacher;
use App\Models\Exam;
use App\Models\ExamPeriod;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ExamRepository
{
    public function __construct()
    {
    }

    public function getExam(string $search, int $page, int $size, int $schoolYearId): LengthAwarePaginator
    {
        $query = Exam::query()->where('school_year_id', $schoolYearId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->with(['schoolYear','examPeriods']);
        if (isset($search) && !empty($search)) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        return $query->paginate($size, ['*'], 'page', $page);
    }

    public function transformGetExam(LengthAwarePaginator $exams): array
    {
        $data = $exams->map(function (Exam $exam) {
            $examPeriods = $exam->examPeriods;
            return [
                "name"         => $exam->name ?? "",
                "schoolYearId" => $exam->school_year_id,
                "schoolYear"   => $exam->schoolYear->name ?? "",
                "point"        => $exam->point,
                "examPeriod"   => $examPeriods->map(function (ExamPeriod $examPeriod) {
                    return [
                        'id' => $examPeriod->id,
                        'name' => $examPeriod->name,
                        'date' => $examPeriod->date,
                    ];
                })
            ];
        })->toArray();
        return [
            'data'       => $data,
            'total'      => $exams->total(),
            'page_index' => $exams->currentPage(),
            'page_size'  => $exams->perPage(),
        ];
    }

    public function storeExam(array $dataInsert)
    {
        return Exam::query()->create($dataInsert);
    }

    public function updateExam(array $dataUpdate, int $examPeriodId): void
    {
        Exam::query()->where('id', $examPeriodId)->update($dataUpdate);
    }

    public function deleteExam(int $examPeriodId): void
    {
        Exam::query()->where('id', $examPeriodId)->update(['is_deleted' => DeleteEnum::DELETED->value]);
    }

    public function getSubject(array $subjectIds = []): Collection
    {
        $query = Subject::query()->where('is_deleted', DeleteEnum::NOT_DELETE->value)->get();
        if (!empty($subjectIds)) {
            $query = $query->whereIn('id', $subjectIds);
        }
        return $query->get();
    }

    public function getClassSubjectTeacher(int $userId, int $classId)
    {
        return ClassSubjectTeacher::query()->where('user_id', $userId)
            ->where('class_id', $classId)
            ->whereNull('end_date')
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->where('status', StatusEnum::ACTIVE->value)
            ->first();
    }

    public function getSubjectIds($classSubjectTeacher){
        $classSubjectId = $classSubjectTeacher->class_subject_id;
        return  ClassSubject::query()->where('id', $classSubjectId)
            ->where('status', StatusEnum::ACTIVE->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->first()->id ?? 0;
    }

    public function getSubjectIdsByClassId($classId): array
    {
        return  ClassSubject::query()
            ->where('class_id', $classId)
            ->where('status', StatusEnum::ACTIVE->value)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get()->pluck('subject_id')->toArray();
    }

    public function transformSubject(Collection $subjects): array
    {
        return $subjects->map(function ($subject){
            return [
              'id' => $subject->id,
              'name' => $subject->name ?? "",
            ];
        })->toArray();
    }
}
