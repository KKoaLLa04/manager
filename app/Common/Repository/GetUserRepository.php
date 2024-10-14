<?php


namespace App\Common\Repository;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Models\User;
use Illuminate\Support\Collection;

class GetUserRepository {

    public function getUser (int $user_id, int $type) {
        return User::where('id', $user_id)->where('access_type', $type)->where('status', StatusEnum::ACTIVE->value)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->first();
    }

    public function getManager (int $user_id) {
        return User::where('id', $user_id)->where('access_type', AccessTypeEnum::MANAGER->value)
            ->where('status', StatusEnum::ACTIVE->value)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->first();
    }

    public function getTeachers(): Collection
    {
        return User::query()->where('access_type', AccessTypeEnum::TEACHER->value)->where('status',
            StatusEnum::ACTIVE->value)->where('is_deleted', DeleteEnum::NOT_DELETE->value)->get();
    }

}
