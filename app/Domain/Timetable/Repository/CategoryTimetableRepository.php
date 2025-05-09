<?php

namespace App\Domain\Timetable\Repository;

use App\Common\Enums\DeleteEnum;
use App\Models\CategoryAttendance;

class CategoryTimetableRepository
{

    public function checkCategoryTimetableExists($fromDate, $toDate, $id = 0): bool
    {
        $query = CategoryAttendance::query()
            ->where('from_date', '<=', $toDate)
            ->where('to_date', '>=', $fromDate)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value);
        if ($id != 0) {
            return $query->whereNot('id', $id)->exists();
        }
        return $query->exists();
    }

    public function update($data, $id): void
    {
        CategoryAttendance::query()
            ->where('id', $id)
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->update($data);

    }
    public function create($data): void
    {
        CategoryAttendance::query()
            ->create($data);

    }

    public function deleted($id): void
    {
        CategoryAttendance::query()
            ->where('id', $id)
            ->where('is_deleted', 0)
            ->update(['is_deleted' => DeleteEnum::DELETED->value]);

    }
}
