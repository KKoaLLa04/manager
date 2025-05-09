<?php

namespace App\Domain\Point\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePointStudentRequest extends FormRequest
{
    public function __construct()
    {
    }

    public function rules(): array
    {
        return [
            "classId" => "required|integer|exists:classes,id",
            "subjectId" => "required|integer|exists:subjects,id",
            "data"    => "required|array",
        ];
    }

    public function messages(): array
    {
        return [
        ];
    }

}
