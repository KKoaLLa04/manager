<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    use HasFactory;
    protected $table = 'user_device';

    protected $fillable = [
        'id',
        'user_id',
        'device_token',
        'device_type',
        'status',
        'created_at',
        'updated_at',
    ];
}
