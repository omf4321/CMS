<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Spatie\ResponseCache\Facades\ResponseCache;

class exam_list extends Model
{
    protected $guarded = [
        'id'
    ];
    public $timestamps = true;

    public function student_marks()
    {
        return $this->hasMany(student_exam_mark::class,'exam_list_id');
    }

    public function schedules()
    {
        return $this->belongsTo(daily_schedule::class,'schedule_id');
    }

    public function batches()
    {
        return $this->belongsTo(batch::class, 'batch_id');
    }

    public function invigilators()
    {
        return $this->belongsTo(teacher::class, 'invigilator_id');
    }

    public function scripters()
    {
        return $this->belongsTo(teacher::class, 'scripter_id');
    }

    public function exam_types()
    {
        return $this->belongsTo(exam_type::class,'exam_type_id');
    }
    
}
