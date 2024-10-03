<?php

namespace App\Domain\Class\Repository;

use App\Common\Enums\StatusEnum;
use App\Domain\SchoolYear\Models\SchoolYear;
use App\Models\Classes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassRepository
{

    /**
     * @param  int  $schoolYearId
     * @return bool
     */
    public function checkSchoolYearId(int $schoolYearId): bool
    {
        return SchoolYear::where('id', $schoolYearId)->where('status', StatusEnum::ACTIVE->value)->exists();
    }

    public function getClasses(Request $request)
    {
        $schoolYearId = $request->school_year_id;
        $userId = Auth::user()->id;
        $page = isset($request->page) ? $request->page : 1;
        $size = isset($request->size) ? $request->size : 0;
        $search = isset($request->search) ? $request->search : '';

        $totalPages = ceil(Classes::count() / $size);
    }
}
