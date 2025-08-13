<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class online_exam_subject extends Model
{
    protected $guarded = ['id'];

    public function getCreatedAtAttribute( $value ) {
      return Carbon::parse($value)->format('d M y');
    }

    public function questions()
    {
    	return $this->hasMany(question::class,'subject_id');
    }
    
}
