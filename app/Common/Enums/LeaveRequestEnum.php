<?php

namespace App\Common\Enums;

enum LeaveRequestEnum: int
{
    case AWAITING = 0; //chưa xác nhận
    case ACCEPT = 1; // đã đồng ý
    case REJECT = 2; //từ chối

    public static function transform(int $value): string
    {
        return match ($value) {
            self::AWAITING->value        => "Đang xác nhận",
            self::ACCEPT->value     => " đã được chấp nhận",
            self::REJECT->value => " đã bị từ chối/hủy",
        };
    }
   
}