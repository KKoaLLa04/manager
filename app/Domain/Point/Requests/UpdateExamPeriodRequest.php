<?php

namespace App\Domain\Point\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamPeriodRequest extends FormRequest
{
    public function __construct()
    {
    }

    public function rules(): array
    {
        return [
            "exam_id"        => "required|exists:exam,id",
            "exam_period_id" => "required|exists:exam_period,id",
            "date"           => "required|date",
        ];
    }

    public function messages(): array
    {
        return [
        ];
    }

}
