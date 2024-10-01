<?php
namespace App\Domain\Class\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClassRequest extends FormRequest 
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
            