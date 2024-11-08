<?php

namespace App\Common\Repository;

use App\Common\Enums\StatusEnum;
use App\Domain\SchoolYear\Models\SchoolYear;
use Illuminate\Support\Collection;

class GetSchoolYearRepository
{
    public function getSchoolYear(): Collection
    {
        return SchoolYear::select('id','name','end_date','start_date')->where('status', StatusEnum::ACTIVE->value)->get();
    }

    public function checkSchoolYearId(int $schoolYearId): bool
    {
        return SchoolYear::where('id', $schoolYearId)->where('status', StatusEnum::ACTIVE->value)->exists();
    }
}
