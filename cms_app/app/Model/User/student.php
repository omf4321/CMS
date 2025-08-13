<?php

namespace App\Model\User;

use Illuminate\Database\Eloquent\Model;

class student extends Model
{
    public $timestamps = true;

    public function branches()
    {
    	return $this->belongsTo(branch::class,'branch_id');
    }

    public function batches()
    {
    	return $this->belongsTo(batch::class,'batch_id');
    }

    public function courses()
    {
    	return $this->belongsTo(course::class,'course_id');
    }

    public function groups()
    {
    	return $this->belongsTo(group::class,'group_id');
    }

    public function institutions()
    {
    	return $this->belongsTo(institution::class,'institution_id');
    }
}
