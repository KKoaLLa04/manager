<?php
namespace App\Domain\SchoolYear\Repository;

use App\Common\Enums\DeleteEnum;
use App\Domain\SchoolYear\Models\SchoolYear;

class SchoolYearIndexRepository {


    public function handle () {

        $school_years = SchoolYear::select('id', 'name', 'status', 'start_date', 'end_date', 'created_user_id', 'modified_user_id', 'created_at', 'updated_at')->where('is_deleted', DeleteEnum::NOT_DELETE->value)->get();

        if($school_years->count() > 0){
            return $school_years;
        }

        return [];

    }


}
