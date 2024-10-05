<?php
namespace App\Domain\Student\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentUpdateRequest extends FormRequest 
{
    public function __construct()
    {
    }
    
    public function rules(): array
    {
        return [
            'fullname' => 'required|min:3|max:255',
            'address' => 'required|min:5|max:255',
            // 'dob' => 'required|date|after_or_equal:today', // Ngày sinh phải bằng hoặc sau ngày hiện tại
            'dob' => 'required|date|before:today', // Ngày sinh phải nhỏ hơn ngày hiện tại:today'
            'status' => 'required',
            'gender' => 'required',
            'class_id' => 'required|integer|exists:classes,id', // class_id phải tồn tại trong bảng classes

        ];
    }
    
    public function messages(): array
    {
        return [
        'required' => ':attribute là bắt buộc nhập.',
        'min' => ':attribute phải có ít nhất :min ký tự.',  
        'max' => ':attribute không được vượt quá :max ký tự.',
        'integer' => ':attribute phải là số nguyên.',
        'date' => ':attribute phải là ngày hợp lệ.',
        'before' => ':attribute phải nhỏ hơn ngày hiện tại.', 
        'exists' => ':attribute không tồn tại.',
        // 'after_or_equal' => ':attribute phải lớn hơn hoặc bằng ngày hiện tại.',
        ];
    }
    
    public function attributes(): array
    {
        return [
            'fullname' => 'Họ tên',
            'address' => 'Địa chỉ',
            'dob' => 'Ngày sinh',
            'status' => 'Trạng thái',
            'gender' => 'Giới tính',
            'class_id' => 'Lớp học.',
        ];
    }
    
}
            