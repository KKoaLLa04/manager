<?php

namespace App\Common\Enums;

enum StatusClassEnum: int
{
    case HAS_NOT_HAPPENED = 0;
    case HAS_APPROVED = 1;
    case FINISH = 2;

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }

}
