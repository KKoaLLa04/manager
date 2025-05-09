<?php
namespace App\Domain\User\Repository;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\ClassSubjectTeacher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Common\Enums\DeleteEnum;
use Illuminate\Http\Request;

class UserChangePasswordRepository {


    public function handle (int $id, int $user_id, Request $request) {

        $item = User::find($id);

        if(empty($item)) return false;

        $item->password = Hash::make($request->userPassword);
        $item->modified_user_id = $user_id;

        if($item->save()) return true;

        return false;

    }


}
