<?php
namespace App\Domain\Guardian\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardianLayoutTeacherRequest extends FormRequest
{
    public function __construct()
    {
    }

    // Quy tắc xác thực
    public function rules(): array
{
    $rules = [
        'fullname' => ['required'],
        'dob' => ['nullable','timestamp'],
        'phone' => ['regex:/^0[0-9]{9}$/'],
        'status' => ['required', 'integer'],
        'gender' => ['required', 'integer'],
        'email' => ['email', 'unique:users,email'],
    ];

    return $rules;
}


    // Thông báo lỗi
    public function messages(): array
    {
        return [

        ];
    }
}
