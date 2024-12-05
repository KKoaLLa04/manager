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
}
