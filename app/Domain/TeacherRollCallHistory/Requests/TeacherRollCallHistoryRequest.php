<?php
namespace App\Domain\TeacherRollCallHistory\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TeacherRollCallHistoryRequest extends FormRequest 
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
            