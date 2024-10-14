<?php
namespace App\Domain\User\Repository;

use App\Common\Enums\DeleteEnum;
use App\Models\User;

class UserIndexRepository {


    public function handle ($keyword = "") {

        $school_years = User::where('is_deleted', DeleteEnum::NOT_DELETE->value)->where("fullname", "like", "%".$keyword."%")->get();

        if($school_years->count() > 0){
            return $school_years;
        }

        return [];

    }


}
