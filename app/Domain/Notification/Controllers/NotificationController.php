<?php

namespace App\Domain\Notification\Controllers;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Http\Controllers\BaseController;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class NotificationController extends BaseController
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function index()
    {
        if (Auth::user()->access_type != AccessTypeEnum::GUARDIAN->value) {
            return $this->responseError(trans('api.error.not_found'), ResponseAlias::HTTP_UNAUTHORIZED);
        }

        $notifications = UserNotification::query()
            ->where('user_id', Auth::id())
            ->where('is_deleted', DeleteEnum::NOT_DELETE->value)
            ->get();
        $data          = $notifications->map(function ($notification) {
            return [
                'id'         => $notification->id,
                'item_id'    => $notification->item_id,
                'item_type'  => $notification->type,
                'date'       => $notification->date,
                'created_at' => Carbon::parse($notification->created_at)->timestamp,
                'data'       => json_decode($notification->data),
            ];
        });

        return $this->responseSuccess($data, ResponseAlias::HTTP_OK);
    }
}
