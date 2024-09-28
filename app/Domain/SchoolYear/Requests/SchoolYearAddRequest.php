<?php
namespace App\Domain\SchoolYear\Requests;

use App\Domain\SchoolYear\Models\SchoolYear;
use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;




class SchoolYearAddRequest extends FormRequest
{
    public function __construct()
    {
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'min:5',
                'max:225'
            ],
            'status' => [
                'required'
            ],
            'start_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) {
                    $inputYear = Carbon::parse($value)->year;
                    $currentYear = Carbon::now()->year;
                    if ($inputYear < $currentYear) {
                        $fail('Thời gian bắt đầu phải lớn hơn hoặc bằng thời gian hiện tại');
                    }
                },
                function ($attribute, $value, $fail) {
                    $school_years = SchoolYear::all();
                    if($school_years->count() > 0){
                        $school_year_last = $school_years->last();
                        $time1 = Carbon::parse($value);
                        $time2 = Carbon::parse($school_year_last->end_date);
                        if ($time1 == $time2) {
                            $fail('Năm bắt đầu không được bằng năm kết thúc của năm học trước đó.');
                        }
                    }
                },
            ],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:now',
                'after_or_equal:start_year'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'required' => ':attribute bắt buộc phải nhập',
            'min' => ':attribute không được bé hơn :min ký tự',
            'max' => ':attribute không được lớn hơn :max ký tự',
            'date' => ':attribute phải là kiểu thời gian',
            'after_or_equal' => ':attribute phải lớn hơn hoặc bằng thời gian hiện tại',
            'after' => ':attribute phải lớn hơn thời gian bắt đầu'
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Tên năm học',
            'status' => 'Trang thái',
            'start_year' => 'Thời gian bắt đầu',
            'end_year' => 'Thời gian kết thúc'
        ];
    }

}
