<?php

namespace App\jobs;

use App\Common\Enums\StatusStudentEnum;
use App\Domain\RollCall\Models\RollCall;
use App\Models\Student;
use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotificationJob
{
    use Dispatchable, InteractsWithQueue, Queueable ,SerializesModels;

    private RollCall $notification;
    public function __construct($notification)
    {
        $this->notification = $notification;
    }

    public function handle(){
        Log::info("create notification job");
        dd(13);
        $studentId = $this->notification->student_id;
        $student = Student::query()->where('id',$studentId ?? 0)->with('parents')->first();

        $dataNoti = [
            "title" => "Học sinh: " . $student->fullname . StatusStudentEnum::transform($this->notification->status),
            "title_en" => "Học sinh: " . $student->fullname . StatusStudentEnum::transform($this->notification->status),
            "class_id" =>  $this->notification->class_id ?? 0,
            "time"     => now()
        ];

        $data = [
            "item_id" => $this->notification->id,
            "type" => 1,
            "is_read" => 0,
            "is_send" => 0,
            "is_convert" => 0,
            "data" => json_encode($dataNoti),
            "date" => now()
        ];
        UserNotification::query()->create($data);
    }
}
