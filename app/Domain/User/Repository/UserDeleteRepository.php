<?php
namespace App\Domain\User\Repository;

use App\Common\Enums\DeleteEnum;
use App\Models\User;
class UserDeleteRepository {


    public function handle (int $id, int $user_id) {

        $item = User::find($id);

        if($item){

            $item->is_deleted = DeleteEnum::DELETED->value;
            $item->modified_user_id = $user_id;

            if($item->save()) return true;

            return false;

        }

        return false;

    }


}
