<?php
namespace App\Domain\SchoolYear\Repository;
use App\Domain\SchoolYear\Requests\SchoolYearEditRequest;
use App\Domain\SchoolYear\Models\SchoolYear;

use function PHPUnit\Framework\returnSelf;

class SchoolYearEditRepository {


    public function handle (int $id, int $user_id, SchoolYearEditRequest $request) {

        $request->validated();

        $item = SchoolYear::find($id);

        if (!$item) return false;

        $item->name = $request->schoolYearName;
        $item->status = $request->schoolYearStatus;
        $item->modified_user_id = $user_id;

        if($item->save()) return true;

        return false;

    }


}
