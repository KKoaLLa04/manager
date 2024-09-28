<?php


namespace App\Common\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Models\User;

class GetUserRepository {

    public function getUser (int $user_id, int $type) {
        return User::where('id', $user_id)->where('access_type', $type)->where('status', StatusEnum::ACTIVE->value)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->first();
    }



}
