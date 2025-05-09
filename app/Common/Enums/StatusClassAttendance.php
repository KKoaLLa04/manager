<?php

namespace App\Common\Enums;

enum StatusClassAttendance: int
{
    case HAS_CHECKED = 1;

    case NOT_YET_CHECKED = 2;
}