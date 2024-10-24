<?php
namespace App\Domain\SchoolYear\Repository;
use App\Domain\SchoolYear\Models\SchoolYear;

class SchoolYearDetailRepository {


    public function handle (int $id) {

        $school_year = SchoolYear::where('is_deleted', 0)->where('id', $id)->first();

        if($school_year){

            $mapped = collect([$school_year])->map(function ($item) {
                return [
                    "schoolYearId" => $item->id,
                    "schoolYearName" => $item->name,
                    "schoolYearStatus" => $item->status,
                    "schoolYearStartDate" => strtotime($item->start_date),
                    "schoolYearEndDate" => strtotime($item->end_date,)
                ];
            })->first(); // Lấy lại đối tượng sau khi map

            return $mapped;
        }

        return null;

    }


}
