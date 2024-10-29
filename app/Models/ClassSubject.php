<?php

namespace App\Models;

use App\Common\Enums\DeleteEnum;
use App\Domain\Subject\Models\Subject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClassSubject extends Model
{
    protected $table = 'class_subject';
    protected $fillable = [
        'id',
        'class_id',
        'subject_id',
        'status',
        'is_deleted',
        'created_user_id',
        'modified_user_id',
        'created_at',
        'updated_at',
    ];

    public function subject(): HasOne
    {
        return $this->hasOne(Subject::class, 'id', 'subject_id')
            ->where("is_deleted", DeleteEnum::NOT_DELETE->value);
    }
}
