<?php
namespace App\Domain\StatisAttendance\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StatisAttendanceRequest extends FormRequest 
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
            