<?php
namespace App\Domain\Point\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetExamRequest extends FormRequest
{
    public function __construct()
    {
    }
    
    public function rules(): array
    {
        return [
            "school_year_id" => "required|integer|exists:school_year,id",
            "page"   => "integer|nullable",
            "size"   => "integer|nullable",
            "search" => "string|nullable",
        ];
    }
    
    public function messages(): array
    {
        return [
        ];
    }
    
}
