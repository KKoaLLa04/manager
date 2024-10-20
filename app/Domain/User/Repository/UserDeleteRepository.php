<?php
namespace App\Domain\User\Repository;

use App\Common\Enums\DeleteEnum;
use App\Models\User;
use App\Models\ClassSubjectTeacher;
use Carbon\Carbon;
use App\Common\Enums\StatusEnum;
use Illuminate\Support\Facades\Auth;
use App\Common\Enums\StatusTeacherEnum;


class UserDeleteRepository {


    public function handle (int $id, int $user_id) {

        $item = User::find($id);

        if($item){

            $item->is_deleted = DeleteEnum::DELETED->value;
            $item->modified_user_id = $user_id;

            if($item->save()){


                $checkTearchMainHasClass = ClassSubjectTeacher::where('user_id', $item->id)->where('end_date', null)->where('access_type', StatusTeacherEnum::MAIN_TEACHER->value)->where('status', StatusEnum::ACTIVE->value)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->first();

                if($checkTearchMainHasClass){

                    $itemTearchMainHasClass = ClassSubjectTeacher::find($checkTearchMainHasClass->id);

                    $itemTearchMainHasClass->end_date = Carbon::now();
                    $itemTearchMainHasClass->status = StatusEnum::UN_ACTIVE->value;
                    $itemTearchMainHasClass->is_deleted = DeleteEnum::DELETED->value;
                    $itemTearchMainHasClass->modified_user_id = Auth::user()->id;

                    $itemTearchMainHasClass->save();

                }

                return true;

            };

            return false;

        }

        return false;

    }


}
