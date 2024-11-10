<?php
namespace App\Domain\AcademicYear\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
class AcademicYearRequest extends FormRequest 
{
    public function __construct()
    {
    }
    
    public function rules(): array
    {
        return [
            'name' => ['required', 'min:6', 'max:255', 'unique:academic_year,name'],
            'start_year' => ['required', 'date', 'after_or_equal:today'],
            'end_year' => ['required', 'date', 'after_or_equal:start_year', function($attribute, $value, $fail) {
                $startYear = \Carbon\Carbon::parse($this->start_year)->year;
                $endYear = \Carbon\Carbon::parse($value)->year;
                if (($endYear - $startYear) != 4) {
                    $fail('Năm kết thúc phải cách năm bắt đầu đúng 4 năm.');
                }
            }],
            'status' => ['required']
        ];
    }
    
    public function messages(): array
    {
        return [
            'name.required' => 'Tên niên khóa là bắt buộc.',
            'name.min' => 'Tên niên khóa phải có ít nhất 6 ký tự.',
            'name.max' => 'Tên niên khóa không được vượt quá 255 ký tự.',
            'name.unique' => 'Tên niên khóa này đã tồn tại.',
            'start_year.required' => 'Năm bắt đầu là bắt buộc.',
            'start_year.date' => 'Năm bắt đầu phải là định dạng ngày hợp lệ.',
            'start_year.after_or_equal' => 'Năm bắt đầu phải lớn hơn hoặc bằng ngày hiện tại.',
            'end_year.required' => 'Năm kết thúc là bắt buộc.',
            'end_year.date' => 'Năm kết thúc phải là định dạng ngày hợp lệ.',
            'end_year.after_or_equal' => 'Năm kết thúc phải lớn hơn hoặc bằng năm bắt đầu.',
            'status.required' => 'Trạng thái bắt buộc phải chọn',
        ];
    }
    
}
            