<?php
namespace App\Domain\LeaveRequest\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LeaveRequestRequest extends FormRequest 
{
    public function __construct()
    {
    }
    
    public function rules(): array
    {
        return [
            'refuse_note' => ['required']
        ];
    }
    
    public function messages(): array
    {
        return [
            'refuse_note.required' => ['Cần phải có lý do mới được hủy đơn']
        ];
    }
    
}
            