<?php

namespace App\Common\Enums;

enum AccessTypeEnum: int
{
    case MANAGER = 1;
    case TEACHER = 2;
    case GUARDIAN = 3;
}
