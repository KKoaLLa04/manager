<?php
namespace App\Domain\SchoolYear\Repository;
use App\Domain\SchoolYear\Requests\SchoolYearEditRequest;
use App\Domain\SchoolYear\Models\SchoolYear;

class SchoolYearDeleteRepository {


    public function handle (int $id, int $user_id) {

        $item = SchoolYear::find($id);

        if($item){

            $item->is_deleted = 0;
            $item->modified_user_id = $user_id;

            if($item->save()) return true;

            return false;

        }

        return false;

    }


}
