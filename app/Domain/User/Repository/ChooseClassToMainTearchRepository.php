<?php
namespace App\Domain\User\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\MainTearchEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Classes;
use App\Models\ClassSubjectTeacher;

class ChooseClassToMainTearchRepository {


    public function handle ($school_year_id) {

        $classes = Classes::where('school_year_id', $school_year_id)->get();

        $arrIdClassHasMainTearch = [];

        foreach ($classes as $cls) {

            $checkClassHasMainTearch = ClassSubjectTeacher::where('class_id', $cls->id)->where('end_date', null)->where('status', StatusEnum::ACTIVE->value)->where('access_type', StatusTeacherEnum::MAIN_TEACHER->value)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->first();

            if($checkClassHasMainTearch){
                $arrIdClassHasMainTearch[] = $cls->id;
            }

        }

        $classeNoHasMainTearch = Classes::whereNotIn('id', $arrIdClassHasMainTearch)->get();

        if($classeNoHasMainTearch->toArray()) return $classeNoHasMainTearch->toArray();

        return [];

    }


}
