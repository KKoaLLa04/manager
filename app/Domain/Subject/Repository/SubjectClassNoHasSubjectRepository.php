<?php
namespace App\Domain\Subject\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Domain\Subject\Models\Subject;
use App\Models\Classes;
use App\Models\ClassSubject;

class SubjectClassNoHasSubjectRepository {


    public function handle ($class_id) {

        $subjects = Subject::all();

        $classHasSubject = ClassSubject::where('class_id', $class_id)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->where('status', StatusEnum::ACTIVE->value)->get();

        if($classHasSubject->count() > 0){

            $idSubjectClassHasSubject = $classHasSubject->map(function ($item) {
                return $item->subject_id;
            });

            return $subjects->map(function ($item) use ($idSubjectClassHasSubject) {
                if (!in_array($item->id, $idSubjectClassHasSubject->toArray())) {
                    return [
                        "subjectId" => $item->id,
                        "subjectName" => $item->name,
                    ];
                }
            });

        }else{
            return $subjects->map(function ($item) {
                return [
                    "subjectId" => $item->id,
                    "subjectName" => $item->name,
                ];
            });;
        }

    }


}
