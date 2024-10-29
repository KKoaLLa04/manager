<?php

namespace App\Domain\Class\Requests;

use App\Common\Enums\StatusClassEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherForSubjectOfClassRequest extends FormRequest
{
    public function __construct()
    {
        parent::__construct();
    }

    public function rules(): array
    {
        return [
            "class_id"         => ["required", 'integer', "exists:classes,id"],
            "teacher_id"       => ["required", "integer", "exists:users,id"],
            "class_subject_id" => ["required", "integer", "exists:class_subject,id"],
        ];
    }

    public function messages(): array
    {
        return [
        ];
    }
}
