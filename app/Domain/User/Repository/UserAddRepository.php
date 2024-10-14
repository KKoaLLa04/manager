<?php

namespace App\Domain\User\Repository;

use App\Common\Enums\DeleteEnum;
use App\Domain\User\Requests\UserAddRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserAddRepository {


    public function handle (int $user_id, UserAddRequest $request) {

        $request->validated();

        $item = new User();

        $item->fullname = $request->fullname;
        $item->username = $request->username;
        $item->email = $request->email;
        $item->password = Hash::make($request->password);
        $item->phone = $request->phone;
        $item->address = $request->address;
        $item->access_type = $request->access_type;
        $item->dob = $request->dob;
        $item->status = $request->status;
        $item->gender = $request->gender;
        // $item->active = $request->active;
        $item->is_deleted = DeleteEnum::NOT_DELETE->value;
        $item->created_user_id = $user_id;
        $item->code = "NV".time();

        if($item->save()) return true;

        return false;

    }


}
