<?php

namespace App\Domain\StatisAttendance\Repository;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusStudentEnum;
use App\Domain\StatisAttendance\Models\StatisAttendance;
use App\Models\Classes;
use Carbon\Carbon;

class StatisAttendanceTeacherResponsitory
{
    public function getAllAttendanceTeachersOnDay(){
        $today = now();
        $allAtten = StatisAttendance::get();
    }
}
