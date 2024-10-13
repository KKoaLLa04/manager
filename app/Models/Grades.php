<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grades extends Model
{
    use HasFactory;

    protected $table = 'grades';

    protected $fillable = [
        'id',
        'name',
    ];

    public function classes()
    {
        return $this->hasMany(Classes::class, 'grade_id');
    }
}
