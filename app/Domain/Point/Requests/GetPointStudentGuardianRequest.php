<?php

namespace App\Domain\Point\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetPointStudentGuardianRequest extends FormRequest
{
    public function __construct()
    {
    }

    public function rules(): array
    {
        return [
            "school_year_id" => "required|integer|exists:school_year,id",
            "student_id"       => "required|integer|exists:students,id",
        ];
    }

    public function messages(): array
    {
        return [
        ];
    }

}
