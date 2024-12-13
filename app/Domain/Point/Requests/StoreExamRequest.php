<?php

namespace App\Domain\Point\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRequest extends FormRequest
{
    public function __construct()
    {
    }

    public function rules(): array
    {
        return [
            "school_year_id" => "required|integer|exists:school_year,id",
            "name"           => "required|string",
            "point"          => "required|integer",
        ];
    }

    public function messages(): array
    {
        return [
        ];
    }

}
