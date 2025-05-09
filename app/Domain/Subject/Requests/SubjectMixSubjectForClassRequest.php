<?php
namespace App\Domain\Subject\Requests;

use App\Models\ClassSubject;
use Illuminate\Foundation\Http\FormRequest;

class SubjectRequest extends FormRequest
{
    public function __construct()
    {
    }

    public function rules(): array
    {
        return [
            'class_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) {
                    $check = ClassSubject::where('class_id', $this->class_id)->where('subject_id', $this->subject_id)->first();
                    if($check) $fail(trans('api.error.subject.have_subject_class'));
                },
            ],
            'subject_id' => 'required|integer',
        ];
    }

    public function messages(): array
    {
        return [
            'integer' => trans('api.error.integer'),
            'required' => trans('api.error.required'),
        ];
    }

}
