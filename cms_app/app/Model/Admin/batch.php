<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Spatie\ResponseCache\Facades\ResponseCache;

class batch extends Model
{
    protected $guarded = [
        'id'
    ];
    public $timestamps = true;

    public function getCreatedAtAttribute( $value ) {
      return Carbon::parse($value)->format('d M y');
    }

    public function echelons()
    {
    	return $this->belongsTo(echelon::class,'echelon_id');
    }

    public function branches()
    {
    	return $this->belongsTo(branch::class,'branch_id');
    }

    public function students()
    {
        return $this->hasMany(student::class,'batch_id');
    }

    public function schedules()
    {
        return $this->hasMany(daily_schedule::class, 'batch_id');
    }
    public function attendance_lists()
    {
        return $this->hasMany(attendance_list::class,'batch_id')->whereDate('date', \Carbon\Carbon::today());
    }
    
    public function groups()
    {
    	return $this->belongsTo(group::class,'group_id');
    }

    public function courses()
    {
    	return $this->belongsTo(course::class,'course_id');
    }
}
