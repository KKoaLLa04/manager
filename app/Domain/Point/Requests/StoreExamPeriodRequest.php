<?php

namespace App\Domain\Point\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamPeriodRequest extends FormRequest
{
    public function __construct()
    {
    }

    public function rules(): array
    {
        return [
            "exam_id" => "required|exists:exam,id",
            "date"    => "required",
        ];
    }

    public function messages(): array
    {
        return [
        ];
    }

}
