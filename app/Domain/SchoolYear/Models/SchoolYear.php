<?php

namespace App\Domain\SchoolYear\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    use HasFactory;

    public $table = "school_year";

    protected $fillable = [
        'name',
        'status',
        'start_date',
        'end_date',
        'created_user_id',
        'modified_user_id',
        'created_at',
        'updated_at',
    ];
}
