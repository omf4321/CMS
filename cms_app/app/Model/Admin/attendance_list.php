<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Spatie\ResponseCache\Facades\ResponseCache;
use App\Model\Admin\student_attendance;

class attendance_list extends Model
{
    protected $guarded = [
        'id'
    ];
    public $timestamps = true;

    public function present_students()
    {
        // return student_attendance::class,'attendance_list_id')->where('attendance', 1);
    }

    public function schedules()
    {
        return $this->belongsTo(daily_schedule::class,'schedule_id');
    }
    
}
