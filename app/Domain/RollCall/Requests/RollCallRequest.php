<?php
namespace App\Domain\RollCall\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RollCallRequest extends FormRequest 
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
            