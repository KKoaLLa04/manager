<?php
namespace App\Domain\Student\Requests;

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
            'address' => 'required|string|min:5|max:255',
            'student_code' => 'required|string|min:3|max:20|unique:students,student_code',
            'dob' => 'required|date',
            'status' => 'required',
            'gender' => 'required',
            'class_id' => 'required|integer|exists:classes,id', // class_id phải tồn tại trong bảng classes
        ];
    }
    
    public function messages(): array
    {
        return [
        'fullname.required' => 'Trường họ tên là bắt buộc.',
        'fullname.string' => 'Họ tên phải là chuỗi ký tự hợp lệ.',
        'fullname.min' => 'Họ tên phải có ít nhất :min ký tự.',
        'fullname.max' => 'Họ tên không được vượt quá :max ký tự.',

        'address.required' => 'Trường địa chỉ là bắt buộc.',
        'address.string' => 'Địa chỉ phải là chuỗi ký tự hợp lệ.',
        'address.min' => 'Địa chỉ phải có ít nhất :min ký tự.',
        'address.max' => 'Địa chỉ không được vượt quá :max ký tự.',

        'student_code.required' => 'Trường mã học sinh là bắt buộc.',
        'student_code.string' => 'Mã học sinh phải là chuỗi ký tự hợp lệ.',
        'student_code.min' => 'Mã học sinh phải có ít nhất :min ký tự.',
        'student_code.max' => 'Mã học sinh không được vượt quá :max ký tự.',
        'student_code.unique' => 'Mã học sinh này đã tồn tại, vui lòng chọn mã khác.',

        'dob.required' => 'Trường ngày sinh là bắt buộc.',
        'dob.date' => 'Ngày sinh phải là một ngày hợp lệ.',

        'status.required' => 'Trường trạng thái là bắt buộc.',
       

        'gender.required' => 'Trường giới tính là bắt buộc.',
        
        'class_id.required'=> 'lớp học bắt buộc chọn',
        'class_id.exists'=> 'lớp học không tồn tại.',     
        'class_id.integer' => 'Lớp học phải là số nguyên.',
        ];
    }
    
}
            