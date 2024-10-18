<?php
namespace App\Domain\SchoolYear\Repository;

use App\Common\Enums\DeleteEnum;
use App\Domain\SchoolYear\Models\SchoolYear;

class SchoolYearIndexRepository {


    public function handle ($keyword = "") {

        $school_years = SchoolYear::where('is_deleted', DeleteEnum::NOT_DELETE->value)->where("name", "like", "%".$keyword."%")->get();

        if($school_years->count() > 0){
            return $school_years->map(function ($item, $index) {
                return [
                    "schoolYearId" => $item->id,
                    "schoolYearName" => $item->name,
                    "schoolYearStatus" => $item->status,
                    "schoolYearStartDate" => strtotime($item->start_date),
                    "schoolYearEndDate" => strtotime($item->end_date,)
                ];
            });
        }

        return [];

    }


}
