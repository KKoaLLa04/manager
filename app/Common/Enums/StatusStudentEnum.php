<?php

namespace App\Common\Enums;

enum StatusStudentEnum: int
{
    case PRESENT        = 1; // có mặt
    case UN_PRESENT     = 2; //Nghỉ không phép
    case UN_PRESENT_PER = 3; //Nghỉ học có phép
    case LATE           = 4; // đi muộn
    case HOLIDAY        = 5; //ngày lễ 

    public static function transform(int $value): string
    {
        return match ($value) {
            self::PRESENT->value        => "có mặt",
            self::UN_PRESENT->value     => "nghỉ không phép",
            self::UN_PRESENT_PER->value => "nghỉ học có phép",
            self::LATE->value           => "đi muộn",
        };
    }

}
