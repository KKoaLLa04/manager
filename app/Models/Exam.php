<?php

namespace App\Models;

use App\Domain\SchoolYear\Models\SchoolYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Exam extends Model
{
    use HasFactory;
    protected $table = 'exam';

    protected $fillable = [
        'id',
        'name',
        'school_year_id',
        'point',
        'created_by',
        'updated_by',
        'type',
        'status',
        'is_deleted',
        'created_at',
        'updated_at',
    ];

    public function schoolYear(): HasOne
    {
        return $this->hasOne(SchoolYear::class, 'id', 'school_year_id');
    }
}
