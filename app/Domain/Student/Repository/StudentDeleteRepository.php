<?php
namespace App\Domain\Student\Repository;

use App\Common\Enums\DeleteEnum;
use App\Domain\Student\Requests\StudentEditRequest;
use App\Domain\Student\Models\Student;
use App\Models\Student as ModelsStudent;

class StudentDeleteRepository {


    public function handle (int $id, int $user_id) {

        $item = ModelsStudent::find($id);

        if($item){

            $item->is_deleted = DeleteEnum::DELETED->value;
            $item->modified_user_id = $user_id;

            if($item->save()) return true;

            return false;

        }

        return false;

    }


}
