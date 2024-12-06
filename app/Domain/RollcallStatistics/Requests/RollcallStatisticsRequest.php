<?php
namespace App\Domain\RollcallStatistics\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RollcallStatisticsRequest extends FormRequest 
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
            