<?php

namespace App\Domain\SchoolYear\Repository;

use App\Common\Enums\DeleteEnum;
use App\Domain\SchoolYear\Requests\SchoolYearAddRequest;
use App\Domain\SchoolYear\Models\SchoolYear;
use SebastianBergmann\Type\TrueType;

class SchoolYearAddRepository {


    public function handle (int $user_id, SchoolYearAddRequest $request) {

        $request->validated();

        $item = new SchoolYear();

        $item->name = $request->schoolYearName;
        $item->status = $request->schoolYearStatus;
        $item->start_date = $request->schoolYearStartDate;
        $item->end_date = $request->schoolYearEndDate;
        $item->created_user_id = $user_id;
        $item->is_deleted = DeleteEnum::NOT_DELETE->value;

        if($item->save()) return true;

        return false;

    }


}
