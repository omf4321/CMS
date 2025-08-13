<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class question_tag extends Model
{    
    protected $guarded = [
        'id'
    ];

    public function questions()
    {
    	return $this->hasMany(question::class,'question_tag_id');
    }
    
}
