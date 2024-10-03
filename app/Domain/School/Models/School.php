<?php
namespace App\Domain\School\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;
    public $table = 'school';

    protected $fillable = [
        'code', 'name', 'avatar', 'address', 'logo', 'telephone', 'email', 'modified_user_id'
    ];
}
