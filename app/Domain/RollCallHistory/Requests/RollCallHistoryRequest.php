<?php
namespace App\Domain\RollCallHistory\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RollCallHistoryRequest extends FormRequest 
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
            