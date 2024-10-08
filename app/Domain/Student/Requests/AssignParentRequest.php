<?php
namespace App\Domain\Student\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssignParentRequest extends FormRequest 
{
    public function authorize()
    {
        return true; // Chỉnh sửa nếu bạn cần thêm xác thực quyền
    }

    public function rules()
    {
        return [
            'parent_id' => 'required|integer|exists:users,id', // Kiểm tra xem parent_id có tồn tại trong bảng users không
        ];
    }

    public function messages()
    {
        return [
            'parent_id.required' => 'Trường parent_id là bắt buộc.',
            'parent_id.integer' => 'Trường parent_id phải là một số nguyên.',
            'parent_id.exists' => 'Phụ huynh không tồn tại.',
        ];
    }
    
    
}
            