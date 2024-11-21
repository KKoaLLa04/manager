<?php
namespace App\Domain\Student\Requests;

use App\Common\Enums\StatusClassStudentEnum;
use Illuminate\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest 
{
    public function __construct()
    {
    }
    
    public function rules(): array
    {
        return [
            'fullname' => 'required|string|min:3|max:255',
            // 'address' => 'string|min:5|max:255',
            'dob' => 'required|date|before:today',
            'status' => 'nullable|in:1,2', 
            'gender' => 'required|in:1,2', // Chỉ chấp nhận giá trị 1,2
            'class_id' => [
                'required_if:status,1',
                'nullable', 
                'integer', 
                'exists:classes,id',
                function ($attribute, $value, $fail) {
                    if ($this->status == StatusClassStudentEnum::NOT_YET_CLASS->value && $value) {
                        $fail('Không được chọn lớp khi trạng thái là "Chưa vào lớp".');
                    }
                }
            ],
        ];
    }
    
    public function messages(): array
    {
        return [
            'fullname.required' => 'Trường họ tên là bắt buộc.',
            'fullname.string' => 'Họ tên phải là chuỗi ký tự hợp lệ.',
            'fullname.min' => 'Họ tên phải có ít nhất :min ký tự.',
            'fullname.max' => 'Họ tên không được vượt quá :max ký tự.',
    
      
            // 'address.string' => 'Địa chỉ phải là chuỗi ký tự hợp lệ.',
            // 'address.min' => 'Địa chỉ phải có ít nhất :min ký tự.',
            // 'address.max' => 'Địa chỉ không được vượt quá :max ký tự.',
    
            'dob.required' => 'Trường ngày sinh là bắt buộc.',
            'dob.date' => 'Ngày sinh phải là một ngày hợp lệ.',
            'dob.before' => 'Ngày sinh phải trước ngày hôm nay.',
    
            'status.in' => 'Trạng thái chỉ có thể là 1 hoặc 2.',
            'gender.required' => 'Trường giới tính là bắt buộc.',
            'gender.in' => 'Giới tính chỉ có thể là 1 hoặc 2.',
            'class_id.required_if' =>'Yêu cầu chọn lớp cho học sinh khi trạng thái là "Đang học".',
            'class_id.exists' => 'Lớp học không tồn tại.',
            'class_id.integer' => 'Lớp học phải là số nguyên.',
        ];
    }
    
    
    
}
            