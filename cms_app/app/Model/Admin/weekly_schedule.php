<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class weekly_schedule extends Model
{
    protected $gaurded = [
        'id'
    ];
    public $timestamps = true;

    public function subjects()
    {
    	return $this->belongsTo(subject::class,'subject_id');
    }

    public function branches()
    {
    	return $this->belongsTo(branch::class,'branch_id');
    }

    public function batches()
    {
    	return $this->belongsTo(batch::class,'batch_id');
    }
    public function teachers()
    {
        return $this->belongsTo(teacher::class, 'teacher_id');
    }
    
}
