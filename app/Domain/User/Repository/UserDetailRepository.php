<?php
namespace App\Domain\User\Repository;
use App\Models\User;
use App\Common\Enums\DeleteEnum;

class UserDetailRepository {


    public function handle (int $id) {

        $school_year = User::where('is_deleted', DeleteEnum::NOT_DELETE->value)->where('is_deleted', 0)->where('id', $id)->first();

        if($school_year){
            return $school_year;
        }

        return null;

    }


}
