<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class schedule_planner extends Model
{
    protected $gaurded = [
        'id'
    ];
    public $timestamps = true;

    public function batches()
    {
        return $this->belongsTo(batch::class,'batch_id');
    }
    public function subjects()
    {
    	return $this->belongsTo(subject::class,'subject_id');
    }

    public function chapters()
    {
    	return $this->belongsTo(chapter::class,'chapter_id');
    }
    public function courses()
    {
        return $this->belongsTo(course::class,'course_id');
    }  
}
