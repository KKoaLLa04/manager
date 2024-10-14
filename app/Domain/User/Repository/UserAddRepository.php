<?php

namespace App\Domain\User\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\User\Requests\UserAddRequest;
use App\Models\ClassSubjectTeacher;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserAddRepository {


    public function handle (int $user_id, UserAddRequest $request) {

        $request->validated();

        $item = new User();

        $item->fullname = $request->userName;
        $item->username = $request->userUsername;
        $item->email = $request->userEmail;
        $item->password = Hash::make($request->userPassword);
        $item->phone = $request->userPhone;
        $item->address = $request->userAddress;
        $item->access_type = $request->userAccessType;
        $item->dob = $request->userDob;
        $item->status = $request->userStatus;
        $item->gender = $request->userGender;
        $item->is_deleted = DeleteEnum::NOT_DELETE->value;
        $item->created_user_id = $user_id;
        $item->code = "NV".time();

        if($item->save()){

            if(!empty($request->classId)){

                $CSTNew = new ClassSubjectTeacher();

                $CSTNew->class_id = $request->classId;
                $CSTNew->user_id = $item->id;
                $CSTNew->start_date = Carbon::now();
                $CSTNew->status = StatusEnum::ACTIVE->value;
                $CSTNew->access_type = StatusTeacherEnum::MAIN_TEACHER->value;
                $CSTNew->is_deleted = DeleteEnum::NOT_DELETE->value;
                $CSTNew->created_user_id = Auth::user()->id;

                $CSTNew->save();

            }


            return true;

        }

        return false;

    }


}
