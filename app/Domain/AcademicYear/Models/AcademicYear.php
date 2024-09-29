<?php

namespace App\Domain\AcademicYear\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AcademicYear extends Model
{
    use HasFactory;

    public $table = "academic_year";

    protected $fillable = ['name', 'start_year', 'end_year', 'status','code','created_user_id','modified_user_id'];

    public static function generateRandomCode()
    {
        $randomString = strtoupper(Str::random(6)); 
        return 'KH' . $randomString; 
    }

}