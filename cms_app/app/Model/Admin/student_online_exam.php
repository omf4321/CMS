<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class student_online_exam extends Model
{
    protected $guarded = [
        'id'
    ];
    public $timestamps = true;

    public function exam_details()
    {
        return $this->hasOne(exam_imports::class,'student_online_exam_id');
    }
    public function students()
    {
        return $this->belongsTo(student::class,'student_id');
    }

    public function schedules()
    {
        return $this->belongsTo(daily_schedule::class,'schedule_id');
    }
}
