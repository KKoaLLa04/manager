<?php

namespace App\Domain\Point\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetPointStudentRequest extends FormRequest
{
    public function __construct()
    {
    }

    public function rules(): array
    {
        return [
            "school_year_id" => "required|integer|exists:school_year,id",
            "class_id"       => "required|integer|exists:classes,id",
            "subject_id"     => "required|integer|exists:subjects,id",
        ];
    }

    public function messages(): array
    {
        return [
        ];
    }

}
