<?php
namespace App\Domain\ParentSubject\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ParentSubjectRequest extends FormRequest 
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
            