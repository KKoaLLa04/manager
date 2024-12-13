<?php
namespace App\Domain\LeaveRequestGuardian\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequestGuardian extends Model
{
    use HasFactory;
    public $table = 'leave_request';

    protected $fillable = [
        'id','code', 'title', 'note', 'leave_date', 'return_date', 'status', 
        'processed_by', 'student_id', 'parent_user_id', 'class_id', 'refuse_note'
    ];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->code = $model->code ?? 'MDNP' . str_pad(self::max('id') + 1, 4, '0', STR_PAD_LEFT);
            $model->title = $model->title ?? 'ĐƠN XIN PHÉP NGHỈ HỌC';
            $model->time = Carbon::now('Asia/Ho_Chi_Minh')->format('H:i:s');
        });

    }

}
