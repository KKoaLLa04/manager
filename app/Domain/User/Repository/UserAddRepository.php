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
        $item->is_deleted = DeleteEnum::NOT_DELETE->value;
        $item->created_user_id = $user_id;
        $item->code = "NV".time();

        if($item->save()){

            if(!empty($request->class_id)){

                $CSTNew = new ClassSubjectTeacher();

                $CSTNew->class_id = $request->class_id;
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
