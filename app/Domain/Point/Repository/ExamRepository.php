<?php

namespace App\Domain\Point\Repository;

use App\Common\Enums\DeleteEnum;
use App\Models\Exam;
use Illuminate\Pagination\LengthAwarePaginator;

class ExamRepository
{
    public function __construct()
    {
    }

    public function getExam(string $search, int $page, int $size, int $schoolYearId): LengthAwarePaginator
    {
        $query = Exam::query()->where('school_year_id', $schoolYearId)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->with('schoolYear');
        if (isset($search) && !empty($search)) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        return $query->paginate($size, ['*'], 'page', $page);
    }

    public function transformGetExam(LengthAwarePaginator $examPeriods): array
    {
        $data = $examPeriods->map(function (Exam $examPeriod) {
            return [
                "name"         => $examPeriod->name ?? "",
                "schoolYearId" => $examPeriod->school_year_id,
                "schoolYear"   => $examPeriod->schoolYear->name ?? "",
                "point"        => $examPeriod->point,
            ];
        })->toArray();
        return [
            'data'       => $data,
            'total'      => $examPeriods->total(),
            'page_index' => $examPeriods->currentPage(),
            'page_size'  => $examPeriods->perPage(),
        ];
    }

    public function storeExam(array $dataInsert): void
    {
        Exam::query()->create($dataInsert);
    }

    public function updateExam(array $dataUpdate, int $examPeriodId): void
    {
        Exam::query()->where('id', $examPeriodId)->update($dataUpdate);
    }

    public function deleteExam(int $examPeriodId): void
    {
        Exam::query()->where('id', $examPeriodId)->update(['is_deleted' => DeleteEnum::DELETED->value]);
    }
}
