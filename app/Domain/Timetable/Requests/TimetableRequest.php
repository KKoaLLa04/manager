<?php
namespace App\Domain\Timetable\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TimetableRequest extends FormRequest 
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
            