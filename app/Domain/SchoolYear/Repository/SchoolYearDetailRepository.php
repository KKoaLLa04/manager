<?php
namespace App\Domain\SchoolYear\Repository;
use App\Domain\SchoolYear\Models\SchoolYear;

class SchoolYearDetailRepository {


    public function handle (int $id) {

        $school_year = SchoolYear::select('id', 'name', 'status', 'start_date', 'end_date', 'created_user_id', 'modified_user_id', 'created_at', 'updated_at')->where('is_deleted', 0)->where('id', $id)->first();

        if($school_year){
            return $school_year;
        }

        return null;

    }


}
