<?php

namespace App\Domain\Class\Requests;

use App\Common\Enums\StatusClassEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateClassRequest extends FormRequest
{
    public function __construct()
    {
        parent::__construct();
    }

    public function rules(): array
    {
        $status = StatusClassEnum::values();
        return [
            "school_year_id" => ["required",'integer',"exists:school_year,id"],
            "teacher_id"     => ["nullable","exists:users,id"],
            "name"           => ["required","string","max:255"],
            "academic_id"    =>["required","integer","exists:academic_year,id"],
            "status"         => ["required","integer",Rule::in($status)],
            "grade_id"       => ["required","integer","exists:grades,id"],
        ];
    }

    public function messages(): array
    {
        return [
        ];
    }
}
