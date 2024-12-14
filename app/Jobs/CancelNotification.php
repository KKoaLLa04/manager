<?php

namespace App\Jobs;

use App\Common\Enums\LeaveRequestEnum;
use App\Common\Enums\StatusStudentEnum;
use App\Domain\LeaveRequest\Models\LeaveRequest;
use App\Models\Student;
use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CancelNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private LeaveRequest $notification;

    /**
     * Tạo một instance của job.
     *
     * @param LeaveRequest $notification
     */
    public function __construct(LeaveRequest $notification)
    {
        $this->notification = $notification; // Khởi tạo thuộc tính
    }

    /**
     * Xử lý job.
     */
    public function handle(): void
    {
        Log::info("create notification job");

        // Truy cập thuộc tính đã được khởi tạo
        $studentId = $this->notification->student_id;
        $student = Student::query()->where('id', $studentId ?? 0)->with('parents')->first();

        $dataNoti = [
            "title" => "Đơn xin nghỉ học của học sinh: " . $student->fullname . LeaveRequestEnum::transform($this->notification->status),
            "title_en" => "Đơn xin nghỉ học của học sinh: " . $student->fullname . LeaveRequestEnum::transform($this->notification->status),
            "class_id" => $this->notification->class_id ?? 0,
            "time"     => now()
        ];

        $parent = $student->parents->first();
        if (!is_null($parent)) {
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

