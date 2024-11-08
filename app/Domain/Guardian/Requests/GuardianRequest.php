<?php
namespace App\Domain\Guardian\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GuardianRequest extends FormRequest 
{
    public function __construct()
    {
    }

    // Quy tắc xác thực
    public function rules(): array
{
    $rules = [
        'fullname' => ['required'],
        'username' => ['required', 'min:6', 'max:50', 'unique:users,username', 'nullable'],
        'code' => ['unique:users,code'],
        'password' => ['nullable', 'min:6', 'max:50'],
        'confirm_password' => ['nullable', 'min:6', 'max:255', 'same:password'],
        'dob' => ['nullable', 'date'],
        'phone' => ['regex:/^0[0-9]{9}$/'],
        'status' => ['required', 'integer'],
        'gender' => ['required', 'integer'],
        'email' => ['email', 'unique:users,email'],
    ];

    // Nếu đây là yêu cầu cập nhật (update), bỏ qua quy tắc cho 'username'
    if ($this->isMethod('put') || $this->isMethod('patch')) {
        $rules['username'] = ['nullable', 'min:6', 'max:50', 'unique:users,username,' . $this->id]; // Bỏ qua khi cập nhật
    }

    return $rules;
}


    // Thông báo lỗi
    public function messages(): array
    {
        return [
            'fullname.required' => 'Vui lòng nhập tên.',
            'fullname.min' => 'Tên phải có ít nhất :min ký tự.',
            'fullname.max' => 'Tên không được vượt quá :max ký tự.',
            
            'code.unique' => 'Mã đã có người dùng.',

            'username.required' => 'Vui lòng nhập tên đăng nhập.',
            'username.min' => 'Tên đăng nhập phải có ít nhất :min ký tự.',
            'username.max' => 'Tên đăng nhập không được vượt quá :max ký tự.',
            'username.unique' => 'Tên đăng nhập đã được sử dụng, vui lòng chọn tên khác.',
            
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có ít nhất :min ký tự.',
            'password.max' => 'Mật khẩu không được vượt quá :max ký tự.',
            
            'confirm_password.required' => 'Vui lòng xác nhận mật khẩu.',
            'confirm_password.min' => 'Mật khẩu xác nhận phải có ít nhất :min ký tự.',
            'confirm_password.max' => 'Mật khẩu xác nhận không được vượt quá :max ký tự.',
            'confirm_password.same' => 'Mật khẩu xác nhận cần phải giống mật khẩu.',
            
            'dob.date' => 'Ngày sinh không đúng định dạng ngày/tháng/năm.',

            'phone.regex' => 'Số điện thoại không đúng định dạng, phải bắt đầu bằng 0 và đủ 10 chữ số.',
            
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.integer' => 'Trạng thái phải là một số nguyên.',
            
            'gender.required' => 'Vui lòng chọn giới tính.',
            'gender.integer' => 'Giới tính phải là một số nguyên.',
            
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'email.unique' => 'Email đã được sử dụng, vui lòng chọn email khác.',
        ];
    }
}
