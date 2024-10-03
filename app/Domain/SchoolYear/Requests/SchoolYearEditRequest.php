<?php
namespace App\Domain\SchoolYear\Requests;

use App\Domain\SchoolYear\Models\SchoolYear;
use Illuminate\Foundation\Http\FormRequest;

class SchoolYearEditRequest extends FormRequest
{
    public function __construct()
    {
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'min:5',
                'max:225'
            ],
            'status' => [
                'required'
            ],
            'user_id' => [
                'required',
                'integer'
            ],
            'type' => [
                'required',
                'integer'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute bắt buộc phải nhập',
            'min' => ':attribute không được bé hơn :min ký tự',
            'max' => ':attribute không được lớn hơn :max ký tự',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Tên năm học',
            'status' => 'Trang thái',
        ];
    }

}
