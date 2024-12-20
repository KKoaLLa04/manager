<?php

namespace App\Domain\Guardian\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardianLayoutGuardianRequest extends FormRequest
{
    public function __construct() {}

    // Quy tắc xác thực
    public function rules(): array
    {
        $rules = [
            'fullname' => ['required'],
            'dob' => ['nullable', 'date'],
            'phone' => ['regex:/^0[0-9]{9}$/'],
        ];


        return $rules;
    }


    // Thông báo lỗi
    public function messages(): array
    {
        return [
            'fullname.required' => 'Vui lòng nhập tên.',
            'fullname.min' => 'Tên phải có ít nhất :min ký tự.',
            'fullname.max' => 'Tên không được vượt quá :max ký tự.',
            'dob.date' => 'Ngày sinh không đúng định dạng ngày/tháng/năm.',
            'phone.regex' => 'Số điện thoại không đúng định dạng, phải bắt đầu bằng 0 và đủ 10 chữ số.',
        ];
    }
}
