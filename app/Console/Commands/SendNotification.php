<?php

namespace App\Console\Commands;

use App\Common\Enums\ConvertEnum;
use App\Common\Enums\StatusEnum;
use App\Models\ConvertUserNotification;
use App\Models\UserDevice;
use App\Models\UserNotification;
use Carbon\Carbon;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SendNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $notifications = $this->getNotification();
        if (!$notifications->isEmpty()) {
            $notificationIds = $notifications->pluck('id')->toArray();
            $this->updateNotification($notificationIds);
            $userIds     = $notifications->pluck('user_id')->toArray();
            $userDevices = $this->getUserDevice($userIds)->groupBy('user_id');
            $dataConvert = [];
            foreach ($notifications as $notification) {
                $userDeviceOfNotification = $userDevices->get($notification->user_id);
                if (!$userDeviceOfNotification->isEmpty()) {
                    foreach ($userDeviceOfNotification as $userDevice) {
                        $this->sendNotification($userDevice, $notification);
                        $dataConvert[] = [
                            'user_id'         => $userDevice->user_id,
                            'device_token'    => $userDevice->device_token,
                            'notification_id' => $notification->id,
                            'type'            => $notification->type,
                            'data'            => $notification->data,
                            'device_type'     => $userDevice->device_type,
                            'send_at'         => now(),
                            'created_at'      => Carbon::now(),
                            'updated_at'      => Carbon::now(),
                        ];
                    }
                }
            }
            $this->convertNotification($dataConvert);
        }
    }

    private function getNotification(): Collection
    {
        return UserNotification::query()
            ->where('is_convert', ConvertEnum::NOT_CONVERT->value)
            ->where('created_at', '>=', Carbon::today()->startOfDay()->toDateTimeString())
            ->take(500)
            ->get();
    }

    private function getUserDevice(array $userIds): Collection
    {
        return UserDevice::query()
            ->whereIn('user_id', $userIds)
            ->where('status', StatusEnum::ACTIVE->value)
            ->get();
    }

    private function updateNotification(array $notificationIds): void
    {
        UserNotification::query()
            ->whereIn('id', $notificationIds)
            ->update(['is_convert' => ConvertEnum::CONVERTED->value]);
    }

    private function convertNotification(array $dataConvert): void
    {
        $dataConvert = collect($dataConvert)->chunk(500);
        foreach ($dataConvert as $item) {
            ConvertUserNotification::query()->insert($item->toArray());
        }
    }

    private function sendNotification(mixed $userDevice, mixed $notification)
    {
        $credentials = new ServiceAccountCredentials("https://www.googleapis.com/auth/firebase.messaging",
            json_decode(file_get_contents('pvk.json'), true));
        $ch          = curl_init("https://fcm.googleapis.com/v1/projects/manager-96391/messages:send");
        $token       = $credentials->fetchAuthToken(\Google\Auth\HttpHandler\HttpHandlerFactory::build());
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer '.$token['access_token']
        ]);
        $dataNoti =  [
        "type" => $notification->type,
        "itemId" => $notification->item_id,
        "userId" => $notification->user_id,
        "additionalData" => $notification->data,
        ];
        $message = [
            "message" => [
                "token" => $userDevice->device_token,
                "notification" => [
                    "body" => json_encode($dataNoti),
                    "image" => "https://cdn.shopify.com/s/files/1/1061/1924/files/Smiling_with_Sweat_Emoji_Icon_60x60.png?14173495976923716614"

                ]
            ]
        ];

        $data = json_encode($message);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "post");

        curl_exec($ch);
        curl_close($ch);

    }
}
