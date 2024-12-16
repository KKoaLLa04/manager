<?php

namespace App\Common\Enums;

enum GenderEnum: int
{
    case NAM   = 1;
    case WOMAN = 2;

    public static function getGender($value): int
    {
        return match ($value) {
            'Nam' => 1,
            'Nữ'  => 2,
        };
    }

}
