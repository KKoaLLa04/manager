<?php

namespace App\Common\Enums;

enum StatusClassStudentEnum: int
{
    case LEAVE = 0;
    case STUDYING = 1;
    case NOT_YET_CLASS = 2;
    case RESERVED = 3; // kết thúc lớp hiện tại, đang có phát sinh đó là khi đang ở lớp cũ chuyển sang lớp mới thì trạng thái ở lớp cũ sẽ là nghỉ học hay thêm 1 trạng thái mới
    //đó là đã chuyển lớp, vì lần trước điểm danh là lấy theo bảng student_Class_history nếu như nó chuyển lớp sẽ có cả 1 thằng ở 2 lớp 
}
