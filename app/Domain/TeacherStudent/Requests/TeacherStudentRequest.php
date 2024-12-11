<?php
namespace App\Domain\TeacherStudent\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeacherStudentRequest extends FormRequest
{
    public function __construct()
    {
    }

    public function rules(): array
    {
        return [
            'fullname' => 'required|string|min:3|max:255',
            'address' => 'required|string|min:5|max:255',
            'dob' => 'required|date|before:today',
            'status' => 'required',
            'gender' => 'required',
            'class_id' => 'required|integer|exists:classes,id',
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

            'dob.required' => 'Trường ngày sinh là bắt buộc.',
            'dob.date' => 'Ngày sinh phải là một ngày hợp lệ.',
            'dob.before' => 'Ngày sinh phải trước ngày hôm nay.',

            'status.required' => 'Trường trạng thái là bắt buộc.',
            'gender.required' => 'Trường giới tính là bắt buộc.',

            'class_id.required' => 'Lớp học là bắt buộc.',
            'class_id.exists' => 'Lớp học không tồn tại.',
            'class_id.integer' => 'Lớp học phải là số nguyên.',

            // 'phone.digits' => 'Số điện thoại phải có 10 chữ số.',
            // 'phone.regex' => 'Số điện thoại phải bắt đầu bằng các đầu số hợp lệ của Việt Nam (03, 05, 07, 08, 09)',
            // 'phone.unique' => 'Số điện thoại đã tồn tại trong hệ thống.',
        ];
    }


}
