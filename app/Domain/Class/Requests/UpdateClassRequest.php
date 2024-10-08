<?php

namespace App\Domain\Class\Requests;

use App\Common\Enums\StatusClassEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClassRequest extends FormRequest
{
    public function __construct()
    {
        parent::__construct();
    }

    public function rules(): array
    {
        $status = StatusClassEnum::values();
        return [
            "class_id"        => ["required", 'integer', "exists:classes,id"],
            "name"           => ["required","string","max:255"],
            "teacher_id"     => ["required", "integer", "exists:users,id"],
            "status"         => ["required", "integer", Rule::in($status)],
            "grade_id"       => ["required", "integer", "exists:grades,id"],
        ];
    }

    public function messages(): array
    {
        return [
        ];
    }
}
