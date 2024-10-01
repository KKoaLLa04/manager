<?php

namespace App\Domain\SchoolYear\Repository;

use App\Domain\SchoolYear\Requests\SchoolYearAddRequest;
use App\Domain\SchoolYear\Models\SchoolYear;
use SebastianBergmann\Type\TrueType;

class SchoolYearAddRepository {


    public function handle (int $user_id, SchoolYearAddRequest $request) {

        $request->validated();

        $item = new SchoolYear();

        $item->name = $request->name;
        $item->status = $request->status;
        $item->start_date = $request->start_date;
        $item->end_date = $request->end_date;
        $item->created_user_id = $user_id;

        if($item->save()) return true;

        return false;

    }


}
