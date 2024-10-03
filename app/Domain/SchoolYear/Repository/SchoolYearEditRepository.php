<?php
namespace App\Domain\SchoolYear\Repository;
use App\Domain\SchoolYear\Requests\SchoolYearEditRequest;
use App\Domain\SchoolYear\Models\SchoolYear;

class SchoolYearEditRepository {


    public function handle (int $id, int $user_id, SchoolYearEditRequest $request) {

        $request->validated();

        $item = SchoolYear::find($id);

        $item->name = $request->name;
        $item->status = $request->status;
        $item->modified_user_id = $user_id;

        if($item->save()) return true;

        return false;

    }


}
