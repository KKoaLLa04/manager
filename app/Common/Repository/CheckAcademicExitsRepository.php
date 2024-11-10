<?php

namespace App\Common\Repository;

use App\Common\Enums\StatusEnum;
use App\Domain\AcademicYear\Models\AcademicYear;
use App\Domain\SchoolYear\Models\SchoolYear;

class CheckAcademicExitsRepository
{

    public function checkAcademicId(int $academicId): bool
    {
        return AcademicYear::where('id', $academicId)->where('status', StatusEnum::ACTIVE->value)->exists();
    }
}
