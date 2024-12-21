<?php

namespace App\Jobs;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\LeaveRequestEnum;
use App\Domain\LeaveRequestGuardian\Models\LeaveRequestGuardian;
use App\Models\Student;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CancelParentNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $notificationId;

    /**
     * Create a new job instance.
     * 
     * @param int $notificationId
     */
    public function __construct(int $notificationId)
    {
        $this->notificationId = $notificationId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("CancelParentNotification job executed");

        // Lấy thông tin đơn xin nghỉ của phụ huynh
        $leaveRequest = LeaveRequestGuardian::find($this->notificationId);

        // Kiểm tra xem đơn xin nghỉ có tồn tại không
        if ($leaveRequest) {
            // Lấy thông tin học sinh và phụ huynh
            $student = Student::find($leaveRequest->student_id);
            $parent = $student->parents->first();

            if ($parent) {
                // Lấy thông tin quản lý trường (Giả sử quản lý trường có user type là 'admin')
                $admin = User::where('access_type', AccessTypeEnum::MANAGER->value)
                    ->orWhere('access_type', AccessTypeEnum::TEACHER->value)
                    ->first();

                if ($admin) {
                    // Tạo dữ liệu thông báo cho quản lý trường
                    $dataNoti = [
                        "title" => "Đơn xin nghỉ của học sinh " . $student->fullname . " đã bị hủy",
                        "title_en" => "The parent has canceled the leave request for student: " . $student->fullname,
                        "class_id" => $leaveRequest->class_id,
                        "time" => now()
                    ];

                    // Lưu thông báo cho quản lý trường
                    UserNotification::create([
                        'user_id' => $admin->id,
                        'item_id' => $leaveRequest->id,
                        'type' => 1, // Thông báo cho quản lý
                        'is_read' => 0,
                        'is_send' => 0,
                        'is_convert' => 0,
                        'data' => json_encode($dataNoti),
                        'date' => now()
                    ]);

                    Log::info("Notification sent to the admin about the canceled leave request");
                }
            }
        }
    }
}
