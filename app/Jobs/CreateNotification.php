<?php

namespace App\Jobs;

use App\Common\Enums\StatusStudentEnum;
use App\Domain\RollCall\Models\RollCall;
use App\Models\Student;
use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("create notification job");
        $studentId = $this->notification->student_id;
        $student = Student::query()->where('id',$studentId ?? 0)->with('parents')->first();

        $dataNoti = [
            "title" => "Học sinh: " . $student->fullname . StatusStudentEnum::transform($this->notification->status),
            "title_en" => "Học sinh: " . $student->fullname . StatusStudentEnum::transform($this->notification->status),
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
            UserNotification::query()->create($data);
        }

    }
}
