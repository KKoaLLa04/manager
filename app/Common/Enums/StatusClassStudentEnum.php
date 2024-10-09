<?php

namespace App\Common\Enums;

enum StatusClassStudentEnum: int
{
    case LEAVE = 0;
    case STUDYING = 1;
    case NOT_YET_CLASS = 2;
    case RESERVED = 3;
}
