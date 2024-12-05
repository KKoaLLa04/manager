<?php

namespace App\Domain\Point\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteExamRequest extends FormRequest
{
    public function __construct()
    {
    }

    public function rules(): array
    {
        return [
            "exam_period_id" => "required|exists:exam_period,id",
        ];
    }

    public function messages(): array
    {
        return [
        ];
    }

}
