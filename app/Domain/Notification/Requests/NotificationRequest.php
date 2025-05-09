<?php
namespace App\Domain\Notification\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationRequest extends FormRequest 
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
            