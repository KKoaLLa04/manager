<?php
namespace App\Domain\Subject\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubjectRequest extends FormRequest 
{
    public function __construct()
    {
    }
    
    public function rules(): array
    {
        return [
        ];
    }
    
    public function messages(): array
    {
        return [
        ];
    }
    
}
            