<?php
namespace App\Domain\SchoolYear\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SchoolYearRequest extends FormRequest
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
