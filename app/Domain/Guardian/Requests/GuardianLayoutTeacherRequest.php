<?php

namespace App\Domain\Guardian\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuardianLayoutTeacherRequest extends FormRequest
{
    public function __construct() {}

    // Quy tắc xác thực
    public function rules(): array
    {
        $rules = [
            'fullname' => ['required'],
            'dob' => ['nullable', 'date'],
            'phone' => ['regex:/^0[0-9]{9}$/'],
            'status' => ['required', 'integer'],
            'gender' => ['required', 'integer'],
            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($this->route('id')),
            ],
        ];

        return $rules;
    }


    // Thông báo lỗi
    public function messages(): array
    {
        return [];
    }
}
