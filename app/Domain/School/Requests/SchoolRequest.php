<?php
namespace App\Domain\School\Requests;

use Illuminate\Foundation\Http\FormRequest;
// |exists:users,id
class SchoolRequest extends FormRequest 
{
    public function __construct()
    {

    }
    
    public function rules(): array
    {
        return [
            'code' => 'required|string|min:3|max:10|unique:school,code,' ,
            'name' => 'required|string|min:3|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'address' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'telephone' => 'required|regex:/^[0-9]{10,11}$/',
            'email' => 'required|email|max:255|unique:school,email,' ,
            'modified_user_id' => 'required'
        ];

    }
    
    public function messages(): array
    {
        return [
            // Messages cho trường 'code'
            'code.required' => 'Trường mã trường học là bắt buộc.',
            'code.string' => 'Mã trường học phải là chuỗi ký tự.',
            'code.min' => 'Mã trường học phải có ít nhất :min ký tự.',
            'code.max' => 'Mã trường học không được vượt quá :max ký tự.',
            'code.unique' => 'Mã trường học này đã tồn tại, vui lòng chọn mã khác.',

            // Messages cho trường 'name'
            'name.required' => 'Trường tên trường học là bắt buộc.',
            'name.string' => 'Tên trường học phải là chuỗi ký tự.',
            'name.min' => 'Tên trường học phải có ít nhất :min ký tự.',
            'name.max' => 'Tên trường học không được vượt quá :max ký tự.',

            // Messages cho trường 'avatar'
            'avatar.url' => 'URL của avatar không hợp lệ.',
            'avatar.image' => 'Avatar phải là một tệp hình ảnh.',
            'avatar.mimes' => 'Avatar phải có định dạng: jpeg, png, jpg, hoặc gif.',
            'avatar.max' => 'Kích thước avatar không được vượt quá 2MB.',

            // Messages cho trường 'address'
            'address.required' => 'Địa chỉ là bắt buộc.',
            'address.string' => 'Địa chỉ phải là chuỗi ký tự.',
            'address.max' => 'Địa chỉ không được vượt quá :max ký tự.',

            // Messages cho trường 'logo'
            'logo.url' => 'URL của logo không hợp lệ.',
            'logo.image' => 'Logo phải là một tệp hình ảnh.',
            'logo.mimes' => 'Logo phải có định dạng: jpeg, png, jpg, hoặc gif.',
            'logo.max' => 'Kích thước logo không được vượt quá 2MB.',

            // Messages cho trường 'telephone'
            'telephone.required' => 'Số điện thoại là bắt buộc.',
            'telephone.regex' => 'Số điện thoại phải có độ dài 10-11 chữ số và chỉ chứa số.',

            // Messages cho trường 'email'
            'email.required' => 'Email là bắt buộc.',
            'email.email' => 'Định dạng email không hợp lệ.',
            'email.max' => 'Email không được vượt quá :max ký tự.',
            'email.unique' => 'Email này đã tồn tại, vui lòng chọn email khác.',

            // Messages cho trường 'modified_user_id'
            'modified_user_id.required' => 'Người dùng chỉnh sửa là bắt buộc.',
            'modified_user_id.exists' => 'Người dùng chỉnh sửa không tồn tại trong hệ thống.',
            
        ];
    }
    
}
            