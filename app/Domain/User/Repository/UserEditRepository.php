<?php
namespace App\Domain\User\Repository;
use App\Domain\User\Requests\UserEditRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserEditRepository {


    public function handle (int $id, int $user_id, UserEditRequest $request) {

        $request->validated();

        $item = User::find($id);

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
        $item->modified_user_id = $user_id;
        // $item->active = $request->active;

        if($item->save()) return true;

        return false;

    }


}
