<?php

namespace App\Domain\Class\Requests;

use App\Common\Enums\StatusClassEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DetailClassRequest extends FormRequest
{
    public function __construct()
    {
        parent::__construct();
    }

    public function rules(): array
    {
        return [
            "class_id"       => ["required","integer","exists:classes,id"],
        ];
    }

    public function messages(): array
    {
        return [
        ];
    }
}
