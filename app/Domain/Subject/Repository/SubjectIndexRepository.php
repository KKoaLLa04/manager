<?php
namespace App\Domain\Subject\Repository;

use App\Common\Enums\DeleteEnum;
use App\Domain\Subject\Models\Subject;

class SubjectIndexRepository {


    public function handle () {

        $lists = Subject::all();

        if($lists->count() > 0){
            return $lists->map(function ($item) {
                return [
                    'subject_id' => $item->id,
                    'subjectName' => $item->name
                ];
            });
        }

        return [];

    }


}
