<?php

namespace App\Domain\Student\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Domain\SchoolYear\Models\SchoolYear;
use App\Domain\Student\Requests\StudentUpdateRequest;
use App\Models\Classes;
use App\Models\Student as ModelsStudent;
use App\Models\StudentClassHistory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use SebastianBergmann\Type\TrueType;

class StudentIndexClassByYearRepository {


    public function handle(int $year_id)
    {

        $list = Classes::where('school_year_id', $year_id)->where('status', StatusEnum::ACTIVE->value)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->get();

        // if ($list->count() > 0) {
            return $list->map(function ($item) {
                return [
                    'classId' => $item->id,
                    'className' => $item->name
                ];
            });
        // }

        // return [];

    }



}
