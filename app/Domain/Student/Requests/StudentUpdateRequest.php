<?php
namespace App\Domain\Student\Requests;

use App\Common\Enums\StatusClassStudentEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class StudentUpdateRequest extends FormRequest 
{
    public function __construct()
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'fullname' => 'required|min:3|max:255',
            'dob' => 'required|date|before:today',
            'status' => 'required|in:0,1,2',
            'gender' => 'required|in:1,2',
            'class_id' => [
                    'nullable',
                    'integer',
                    'exists:classes,id',
                    'required_if:status,' . StatusClassStudentEnum::STUDYING->value,
                    function ($attribute, $value, $fail) {
                        // Lấy giá trị status từ request
                        $status = $this->input('status');
                        
                        if (($status == StatusClassStudentEnum::STUDYING->value || $status == StatusClassStudentEnum::LEAVE->value) 
                        && $this->input('status') == StatusClassStudentEnum::NOT_YET_CLASS->value) {
                        $fail('Không thể chuyển sang trạng thái chưa vào lớp khi học sinh đã có lớp.');
                        }
                    
                    },
                ],
        ];
    }

    public function messages(): array
    {
        return [
            'fullname.required' => 'Họ tên là bắt buộc.',
            'dob.required' => 'Ngày sinh là bắt buộc.',
            'dob.before' => 'Ngày sinh phải nhỏ hơn ngày hiện tại.',
            'status.required' => 'Trạng thái là bắt buộc.',
            'status.in' => 'Trạng thái không hợp lệ.',
            'gender.required' => 'Giới tính là bắt buộc.',
            'gender.in' => 'Giới tính không hợp lệ.',
            'class_id.exists' => 'Lớp học không tồn tại.',
            'class_id.required_if' => 'Lớp học là bắt buộc khi trạng thái là đang học.',
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
            'class_id' => 'Lớp học',
        ];
    }
    
    
}
            