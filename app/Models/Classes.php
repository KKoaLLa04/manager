<?php

namespace App\Models;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\AcademicYear\Models\AcademicYear;
use App\Domain\SchoolYear\Models\SchoolYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Classes extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'id',
        'name',
        'code',
        'school_year_id',
        'academic_year_id',
        'status',
        'grade_id',
        'is_deleted',
        'created_user_id',
        'modified_user_id',
        'created_at',
        'updated_at',
    ];

    public function grade()
    {
        return $this->belongsTo(Grades::class, 'grade_id');
    }

}
