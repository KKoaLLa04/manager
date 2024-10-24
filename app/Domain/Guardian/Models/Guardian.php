<?php
namespace App\Domain\Guardian\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Student;

class Guardian extends Model{
    use HasFactory;

    public $table = 'users';

    protected $fillable = [
        'fullname',
        'phone',
        'code',
        'email',
        'access_type',
        'dob',
        'status',
        'gender',
        'address',
        'career',
        'username',
        'password',
        'is_deleted',
        'created_user_id',
        'modified_user_id',
        'created_at',
        'updated_at',
    ];

    public function students()
{
    return $this->belongsToMany(Student::class, 'user_student', 'user_id', 'student_id');
}

}


