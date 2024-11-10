<?php

namespace App\Common\Repository;

use App\Common\Enums\StatusEnum;
use App\Domain\AcademicYear\Models\AcademicYear;
use App\Models\Grade;

class GradeRepository
{
    public function checkGradeExits(int $gradeId): bool
    {
        return Grade::where('id', $gradeId)->exists();
    }
}
