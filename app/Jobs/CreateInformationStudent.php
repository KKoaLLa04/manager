<?php

namespace App\Jobs;

use App\Common\Enums\AccessTypeEnum;
use App\Common\Enums\DeleteEnum;
use App\Common\Enums\GenderEnum;
use App\Common\Enums\StatusStudentEnum;
use App\Domain\Guardian\Models\Guardian;
use App\Domain\RollCall\Models\RollCall;
use App\Models\Classes;
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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Common\Enums\StatusEnum;
use App\Models\UserDevice;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Mail;

class CreateInformationStudent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $notification;

    /**
     * Create a new job instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }


    private function handle(): void
    {
        foreach ($this->data as $index => $row) {
            if ($index == 0) {
                continue;
            }
            $dob    = Carbon::parse($row[4])->format('Y-m-d');
            $data[] = [
                'className'       => $row[1],
                'studentCode'     => $row[2],
                'studentName'     => $row[3],
                'studentDob'      => $dob,
                'studentGender'   => GenderEnum::getGender($row[5]),
                'studentAddress'  => $row[6],
                'nameGuardian'    => $row[7],
                'addressGuardian' => $row[6],
                'jobGuardian'     => $row[8],
            ];

            $data = collect($data)->groupBy('className');

            foreach ($data as $key => $value) {
                $class = Classes::query()->create([
                    'name' => $key,
                    'code' => $key,
                    'school_year_id' => 1,
                    'academic_year_id' => 2,
                    'grade_id' => 1,
                ]);
                $classId = $class->id;



            }

        }
    }

}
