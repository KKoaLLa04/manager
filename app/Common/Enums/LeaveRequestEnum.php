<?php

namespace App\Common\Enums;

enum LeaveRequestEnum: int
{
    case AWAITING = 0; //chưa xác nhận
    case ACCEPT = 1; // đã đồng ý
    case REJECT = 2; //từ chối
   
}