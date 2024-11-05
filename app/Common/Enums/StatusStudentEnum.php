<?php

namespace App\Common\Enums;

enum StatusStudentEnum: int
{
    case PRESENT = 1; // có mặt
    case UN_PRESENT = 2; //Nghỉ không phép
    case UN_PRESENT_PER = 3; //Nghỉ học có phép
    case LATE = 4; // đi muộn

}
