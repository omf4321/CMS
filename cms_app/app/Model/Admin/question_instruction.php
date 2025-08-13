<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class question_instruction extends Model
{    
    protected $guarded = [
        'id'
    ];
    public $timestamps = false;

    public function subjects()
    {
    	return $this->belongsTo(subject::class,'subject_id');
    }
    
}
