<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    use HasFactory;
    protected $table = 'user_notification';

    protected $fillable = [
        'id',
        'user_id',
        'item_id',
        'type',
        'is_read',
        'is_send',
        'is_convert',
        'data',
        'date',
        'is_deleted',
        'created_at',
        'updated_at',
    ];
}
