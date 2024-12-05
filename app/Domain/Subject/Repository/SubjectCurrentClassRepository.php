<?php
namespace App\Domain\Subject\Repository;

use App\Common\Enums\DeleteEnum;
use App\Domain\Subject\Models\Subject;
use App\Models\Classes;

class SubjectCurrentClassRepository {


    public function handle ($year_id) {

        $lists = Classes::where('school_year_id', $year_id)->get();

        if($lists->count() > 0){
            return $lists->map(function ($item) {
                return [
                    'className' => $item->name,
                    'classId' => $item->id,
                ];
            });
        }

        return [];

    }


}
