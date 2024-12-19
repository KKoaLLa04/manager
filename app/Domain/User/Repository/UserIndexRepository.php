<?php
namespace App\Domain\User\Repository;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Models\User;

class UserIndexRepository {


    public function handle ($keyword = "") {

        $list = User::where('is_deleted', DeleteEnum::NOT_DELETE->value)->whereIn('access_type', [AccessTypeEnum::MANAGER->value, AccessTypeEnum::TEACHER->value])->where("fullname", "like", "%".$keyword."%")->orWhere("code", "like", "%".$keyword."%")->get();

        if($list->count() > 0){
            return $list->map(function ($item) {
                return $item->infoMainTearchWithClass();
            });
        }

        return [];

    }


}
