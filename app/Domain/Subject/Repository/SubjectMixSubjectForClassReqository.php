<?php
namespace App\Domain\Subject\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Domain\Subject\Models\Subject;
use App\Models\ClassSubject;
use Illuminate\Http\Request;

class SubjectMixSubjectForClassReqository {


    public function handle ($user_id, $subjects, Request $request) {

        try {

            foreach ($subjects as $value) {

                $classSubject = new ClassSubject();

                $classSubject->class_id = $request->classId;
                $classSubject->subject_id = $value;
                $classSubject->status = StatusEnum::ACTIVE->value;
                $classSubject->is_deleted = DeleteEnum::NOT_DELETE->value;
                $classSubject->created_user_id = $user_id;

                $classSubject->save();

            }

            return true;

        } catch (\Throwable $th) {

            return false;

        }


    }


}
