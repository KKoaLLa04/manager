<?php
namespace App\Domain\User\Repository;
use App\Domain\User\Requests\UserEditRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\ClassSubjectTeacher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Common\Enums\DeleteEnum;



class UserEditRepository {


    public function handle (int $id, int $user_id, UserEditRequest $request) {

        $request->validated();

        $item = User::find($id);

        $item->fullname = $request->userName;
        // $item->username = $request->username;
        $item->email = $request->userEmail;
        // $item->password = Hash::make($request->password);
        $item->phone = $request->userPhone;
        $item->address = $request->userAddress;
        $item->access_type = $request->userAccessType;
        $item->dob = $request->userDob;
        $item->status = $request->userStatus;
        $item->gender = $request->userGender;
        $item->modified_user_id = $user_id;
        // $item->active = $request->active;

        if($item->save()){

            if(!empty($request->classId)){


                $checkTearchMainHasClass = ClassSubjectTeacher::where('user_id', $item->id)->where('end_date', null)->where('access_type', StatusTeacherEnum::MAIN_TEACHER->value)->where('status', StatusEnum::ACTIVE->value)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->first();

                if($checkTearchMainHasClass){

                    $itemTearchMainHasClass = ClassSubjectTeacher::find($checkTearchMainHasClass->id);

                    $itemTearchMainHasClass->end_date = Carbon::now();
                    $itemTearchMainHasClass->status = StatusEnum::UN_ACTIVE->value;
                    $itemTearchMainHasClass->is_deleted = DeleteEnum::DELETED->value;
                    $itemTearchMainHasClass->modified_user_id = Auth::user()->id;

                    $itemTearchMainHasClass->save();

                }

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

        };

        return false;

    }


}
