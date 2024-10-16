<?php

namespace App\Models;

use App\Domain\AcademicYear\Models\AcademicYear;
use App\Domain\SchoolYear\Models\SchoolYear;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes'; 

    protected $fillable = [
        'name',
        'code',
        'main_teacher',
        'school_year_id',
        'academic_year_id',
        'status',
        'grade_id',
        'is_deleted',
        'created_user_id',
        'modified_user_id',
    ];

    public function students()
    {
        return $this->hasMany(StudentClassHistory::class, 'class_id'); // Quan hệ với bảng StudentClassHistory
    }
    // public function academicYear()
    // {
    //     return $this->belongsTo(AcademicYear::class, 'academicyear_id');
    // }
    public function schoolYearName(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class, 'school_year_id','id'); 
    }
    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id'); 
    }

}