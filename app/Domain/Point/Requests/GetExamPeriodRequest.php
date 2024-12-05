<?php
namespace App\Domain\Point\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetExamPeriodRequest extends FormRequest
{
    public function __construct()
    {
    }
    
    public function rules(): array
    {
        return [
            "exam_id" => "required|exists:exam,id",
        ];
    }
    
    public function messages(): array
    {
        return [
        ];
    }
    
}
