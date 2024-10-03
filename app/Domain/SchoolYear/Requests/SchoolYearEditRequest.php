<?php
namespace App\Domain\SchoolYear\Requests;

use App\Domain\SchoolYear\Models\SchoolYear;
use Illuminate\Foundation\Http\FormRequest;

class SchoolYearEditRequest extends FormRequest
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
            'user_id' => [
                'required',
                'integer'
            ],
            'type' => [
                'required',
                'integer'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'required' => trans('api.error.required'),
            'min' => trans('api.error.min'),
            'max' => trans('api.error.max'),
            'integer' => trans('api.error.integer')
        ];
    }

}
