<?php

namespace App\Domain\Class\Requests;

use App\Common\Enums\StatusClassEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignMainTeacherRequest extends FormRequest
{
    public function __construct()
    {
        parent::__construct();
    }

    public function rules(): array
    {
        return [
            "teacher_id"     => ["required","integer","exists:users,id"],
            "class_id"       => ["required","integer","exists:classes,id"],
        ];
    }

    public function messages(): array
    {
        return [
        ];
    }
}
