<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class daily_schedule extends Model
{
    protected $gaurded = [
        'id'
    ];
    public $timestamps = true;

    public function subjects()
    {
    	return $this->belongsTo(subject::class,'subject_id');
    }

    public function chapters()
    {
        return $this->belongsToMany(chapter::class, 'schedule_chapters', 'schedule_id', 'chapter_id');
    }

    public function batches()
    {
        return $this->belongsTo(batch::class, 'batch_id');
    }

    public function teacher_batches()
    {
        return $this->belongsToMany(batch::class,'schedule_teacher_attendances', 'schedule_id', 'batch_id')->withPivot('teacher_id');
    }

    public function teacher_schedules()
    {
        return $this->hasOne(schedule_teacher_attendance::class, 'schedule_id');
    }

    public function questions()
    {
        return $this->belongsTo(exam_question_list::class, 'exam_question_list_id');
    }

    public function exam_lists()
    {
        return $this->hasOne(exam_list::class, 'schedule_id');
    }

    public function exam_invigilators()
    {
        return $this->hasMany(exam_list::class, 'schedule_id')->where('date', \Carbon\Carbon::today())->whereNotNull('invigilator_id');
    }

    public function echelons()
    {
    	return $this->belongsTo(echelon::class,'echelon_id');
    }

    public function teachers()
    {
        return $this->belongsTo(teacher::class,'teacher_id');
    }

    public function schedules()
    {
        return $this->belongsTo(schedule_list::class,'schedule_id');
    }
    // public function exam_question_types()
    // {
    //     return $this->hasMany(exam_question_type::class,'daily_schedule_id');
    // }
    
    
}
