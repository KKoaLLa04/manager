<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConvertUserNotification extends Model
{
    use HasFactory;
    protected $table = 'convert_user_notification';

    protected $fillable = [
        'id',
        'user_id',
        'notification_id',
        'type',
        'data',
        'device_token',
        'send_at',
        'device_type',
        'created_at',
        'updated_at',
    ];
}
