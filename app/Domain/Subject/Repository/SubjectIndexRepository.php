<?php
namespace App\Domain\Subject\Repository;

use App\Common\Enums\DeleteEnum;
use App\Domain\Subject\Models\Subject;

class SubjectIndexRepository {


    public function handle () {

        $school_years = Subject::all();

        if($school_years->count() > 0){
            return $school_years;
        }

        return [];

    }


}
