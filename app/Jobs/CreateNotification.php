<?php

namespace App\Jobs;

use App\Common\Enums\StatusStudentEnum;
use App\Domain\RollCall\Models\RollCall;
use App\Models\Student;
use App\Common\Enums\WebAppTypeEnum;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Message;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Common\Enums\StatusEnum;
use App\Models\UserDevice;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Mail;

class CreateNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private RollCall $notification;
    /**
     * Create a new job instance.
     */
    public function __construct($notification)
    {
        $this->notification = $notification;
    }

    

    private function getUserDevice(array $userIds): Collection
    {
        return UserDevice::query()
            ->whereIn('user_id', $userIds)
            ->where('status', StatusEnum::ACTIVE->value)
            ->get();
    }

    private function sendNotificationWeb(mixed $userDevice, mixed $notification)
    {
        $credentials = new ServiceAccountCredentials("https://www.googleapis.com/auth/firebase.messaging",
            json_decode(file_get_contents(base_path('pvk.json')), true));
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
                "data" => [
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


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("create notification job");
        $studentId = $this->notification->student_id;
        $student = Student::query()->where('id',$studentId ?? 0)->with('parents')->first();

        $dataNoti = [
            "title" => "Học sinh: " . $student->fullname . " " . StatusStudentEnum::transform($this->notification->status),
            "title_en" => "Học sinh: " . $student->fullname . " " . StatusStudentEnum::transform($this->notification->status),
            "class_id" =>  $this->notification->class_id ?? 0,
            "time"     => now()
        ];

        $parent = $student->parents->first();
        if(!is_null($parent)){
            $data = [
                "user_id" => $parent->id,
                "item_id" => $this->notification->id,
                "type" => 1,
                "is_read" => 0,
                "is_send" => 0,
                "is_convert" => 0,
                "data" => json_encode($dataNoti),
                "date" => now()
            ];

            $notification = UserNotification::query()->create($data);
            
            $userDevices = $this->getUserDevice([$parent->id])->groupBy('user_id');

            $userDeviceOfNotification = $userDevices->get($notification->user_id);
            if (isset($userDeviceOfNotification)) {
                foreach ($userDeviceOfNotification as $userDevice) {
                    if ($userDevice->device_type == WebAppTypeEnum::WEB->value){
                        $this->sendNotificationWeb($userDevice, $notification);
                    }
                    $user = User::query()->where('id',$parent->id)->first();
                    if(!is_null($user)){
                        if(!is_null($user->email)){
                            $this->sendMailNotification($user->email, $dataNoti);

                        }
                    }
                }
            }
        }

    }

    private function sendMailNotification($email, $notification
    ) {

        $toEmail = $email;
        $subject = 'Hệ thống website sổ liên lạc điện tử học sinh techscholl';

        Mail::send('sendNoti', compact('notification'),
            function (Message $message) use ($toEmail, $subject) {
                $message->to($toEmail)->subject($subject);
            });
    }
}
