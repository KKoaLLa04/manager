<?php

namespace App\Domain\Class\Requests;

use App\Common\Enums\StatusClassEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteSubjectOfClassRequest extends FormRequest
{
    public function __construct()
    {
        parent::__construct();
    }

    public function rules(): array
    {
        return [
            "class_id"         => ["required", 'integer', "exists:classes,id"],
            "class_subject_id" => ["required", "integer", "exists:class_subject,id"],
        ];
    }

    public function messages(): array
    {
        return [
        ];
    }
}
