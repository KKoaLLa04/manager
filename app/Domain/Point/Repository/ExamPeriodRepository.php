<?php

namespace App\Domain\Point\Repository;

use App\Common\Enums\DeleteEnum;
use App\Models\ExamPeriod;
use Illuminate\Support\Collection;

class ExamPeriodRepository
{
    public function __construct()
    {
    }

    public function getExamPeriodById(int $id): Collection
    {
        return ExamPeriod::query()->where("exam_id", $id)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get();
    }

    public function transformExamPeriod(Collection $examPeriods): array
    {
        return $examPeriods->map(function (ExamPeriod $examPeriod) {
            return [
                "date" => $examPeriod->date
            ];
        })->toArray();
    }

    public function storeExamPeriod(array $data): void
    {
        ExamPeriod::query()->create($data);
    }

    public function updateExamPeriod(array $dataUpdate, int $exam_period_id): void
    {
        ExamPeriod::query()->where("id", $exam_period_id)->update($dataUpdate);
    }

    public function deleteExamPeriod(int $exam_period_id)
    {
        ExamPeriod::query()->where("id", $exam_period_id)
            ->update(['is_deleted' => DeleteEnum::DELETED->value]);
    }
}
