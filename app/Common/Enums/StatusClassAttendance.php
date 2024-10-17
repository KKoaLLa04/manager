<?php

namespace App\Common\Enums;

enum statusClassAttendance: int
{
    case HAS_CHECKED = 1;

    case NOT_YET_CHECKED = 2;
}