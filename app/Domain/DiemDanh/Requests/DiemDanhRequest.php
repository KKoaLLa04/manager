<?php
namespace App\Domain\DiemDanh\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DiemDanhRequest extends FormRequest 
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
            