<?php

namespace App\Domain\RollCall\Models;

use App\Models\Classes;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class RollCall extends Model
{
    use HasFactory;

    public $table = "roll_call";

    protected $fillable = ['student_id', 'date', 'time', 'note', 'status', 'class_id', 'is_deleted', 'created_user_id', 'modified_user_id'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function class()
    {
        return $this->belongsTo(Classes::class, 'class_id', 'id');
    }
}
