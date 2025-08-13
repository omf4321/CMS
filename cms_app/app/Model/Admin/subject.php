<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class subject extends Model
{
    protected $guarded = ['id'];

    public function getCreatedAtAttribute( $value ) {
      return Carbon::parse($value)->format('d M y');
    }
    	
    public function branches()
    {
        return $this->belongsTo(branch::class,'branch_id');
    }
    public function echelons()
    {
        return $this->belongsTo(echelon::class,'echelon_id');
    }
    public function teachers()
    {
        return $this->belongsTo(teacher::class,'teacher_id');
    }
    public function chapters()
    {
        return $this->hasMany(chapter::class,'subject_id', 'id');
    }
    
}
