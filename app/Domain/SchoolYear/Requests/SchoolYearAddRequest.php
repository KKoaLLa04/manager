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
                        $fail(trans('api.error.school_year.start_date_before_end_date'));
                    }
                },
                function ($attribute, $value, $fail) {
                    $school_years = SchoolYear::all();
                    if($school_years->count() > 0){
                        $school_year_last = $school_years->last();
                        $time1 = Carbon::parse($value);
                        $time2 = Carbon::parse($school_year_last->end_date);
                        if ($time1 == $time2) {
                            $fail(trans('api.error.school_year.start_date_not_equal_end_date_before'));
                        }
                    }
                },
            ],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:now',
                'after_or_equal:start_year'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => trans('api.error.required'),
            'min' => trans('api.error.min'),
            'max' => trans('api.error.max'),
            'integer' => trans('api.error.integer'),
            'date' => trans('api.error.date'),
            'after_or_equal' => trans('api.error.after_or_equal')
        ];
    }

}
