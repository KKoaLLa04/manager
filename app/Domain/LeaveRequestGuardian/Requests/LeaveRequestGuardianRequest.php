<?php
namespace App\Domain\LeaveRequestGuardian\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeaveRequestGuardianRequest extends FormRequest 
{
    public function __construct()
    {
    }

    public function rules(): array
    {
            return [
                'leave_date' => 'required|date',
                'return_date' => 'required|date|after_or_equal:leave_date', 
                'note' => 'nullable',
            ];
    }
    
    public function messages(): array
    {
        return [
            'leave_date.required' => 'Ngày bắt đầu nghỉ phép là bắt buộc.',
            'leave_date.date' => 'Ngày bắt đầu nghỉ phép không hợp lệ.',
            'return_date.required' => 'Ngày quay lại là bắt buộc.',
            'return_date.date' => 'Ngày quay lại không hợp lệ.',
            'return_date.after_or_equal' => 'Ngày quay lại phải lớn hơn hoặc bằng ngày bắt đầu nghỉ phép.',
        ];
    }
    
}
            