<?php

namespace App\Imports;

use App\Common\Enums\AccessTypeEnum;
use App\Domain\Guardian\Models\Guardian;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Carbon\Carbon;

class GuardianImport implements ToModel
{
    use Importable;

    /**
     * Chuyển đổi dữ liệu từ file Excel thành bản ghi trong cơ sở dữ liệu.
     *
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Guardian([
            'fullname' => $row[0], // Cột 1 trong Excel (Tên phụ huynh)
            'phone' => $row[1], // Cột 2 trong Excel (Số điện thoại)
            'email' => $row[2], // Cột 3 trong Excel (Email)
            'dob' => Carbon::parse($row[3]), // Cột 4 trong Excel (Ngày sinh)
            'status' => $row[4], // Cột 5 trong Excel (Trạng thái)
            'gender' => $row[5], // Cột 6 trong Excel (Giới tính)
            'address' => $row[6], // Cột 7 trong Excel (Địa chỉ)
            'career' => $row[7], // Cột 8 trong Excel (Nghề nghiệp)
            'username' => $row[8], // Cột 9 trong Excel (Tên người dùng)
            'password' => bcrypt($row[9]), // Cột 10 trong Excel (Mật khẩu - mã hóa)
            'access_type' => AccessTypeEnum::GUARDIAN->value, 
            'is_deleted' => 0, 
            'created_user_id' => auth()->user()->id, 
        ]);
    }
    


}


