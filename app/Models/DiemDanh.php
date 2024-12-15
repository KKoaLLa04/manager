<?php

namespace App\Models;

use App\Common\Enums\DeleteEnum;
use App\Common\Enums\StatusEnum;
use App\Common\Enums\StatusTeacherEnum;
use App\Domain\AcademicYear\Models\AcademicYear;
use App\Domain\RollCall\Models\RollCall;
use App\Domain\SchoolYear\Models\SchoolYear;
use App\Domain\Subject\Models\Subject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DiemDanh extends Model
{
    protected $table = 'diemdanh';

    public function subject () {
        return $this->belongsTo(
            Subject::class,
            'mon',
            'id'
        );
    }

}

