<?php
namespace App\Domain\LeaveRequestGuardian\Requests;

use Illuminate\Foundation\Http\FormRequest;

class leaveRequest extends FormRequest 
{
    public function __construct()
    {
    }

    public function rules(): array
    {
            return [
                'student_id' => 'required|exists:students,id', 
                'leave_date' => 'required|date',
                'return_date' => 'required|date|after_or_equal:leave_date', 
                'note' => 'nullable',
            ];
    }
    
    public function messages(): array
    {
        return [
            'student_id.required' => trans('api.error.student_id_required'),
            'student_id.exists' => trans('api.error.student_not_found'),
            'leave_date.required' => trans('api.error.leave_date_required'),
            'leave_date.date' => trans('api.error.leave_date_invalid'),
            'return_date.required' => trans('api.error.return_date_required'),
            'return_date.date' => trans('api.error.return_date_invalid'),
            'return_date.after_or_equal' => trans('api.error.return_date_after_leave_date'),
        ];
    }
    
}
            