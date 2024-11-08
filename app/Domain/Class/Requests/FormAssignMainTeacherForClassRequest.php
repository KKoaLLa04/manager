<?php
namespace App\Domain\Class\Requests;

use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class FormAssignMainTeacherForClassRequest extends BaseRequest
{
    public function __construct()
    {
    }

    public function rules(): array
    {
        return [
            "page"   => "integer|nullable",
            "size"   => "integer|nullable",
            "search" => "string|nullable",
        ];
    }

    public function messages(): array
    {
        return [
        ];
    }

}
