<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class schedule_teacher_attendance extends Model
{
    protected $guarded = [
        'id'
    ];
    public $timestamps = false;    
    
    public function schedules()
    {
        return $this->belongsTo(daily_schedule::class,'schedule_id');
    }
    public function batches()
    {
        return $this->belongsTo(batch::class,'batch_id');
    }
    public function exam_lists()
    {
        return $this->belongsTo(exam_list::class,'exam_list_id');
    }
    public function teachers()
    {
        return $this->belongsTo(teacher::class,'teacher_id');
    }
}
