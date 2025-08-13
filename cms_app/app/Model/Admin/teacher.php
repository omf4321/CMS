<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class teacher extends Model
{
    protected $guarded = [
        'id'
    ];
    public $timestamps = true;

    public function getCreatedAtAttribute( $value ) {
      	return Carbon::parse($value)->format('d M y');
    }

    public function branches()
    {
    	return $this->belongsTo(branch::class,'branch_id');
    }

    public function users()
    {
        return $this->belongsTo('\App\User','user_id');
    }

    public function todays_schedule()
    {
        return $this->hasMany('\App\Model\Admin\daily_schedule','teacher_id')->whereDate('date', \Carbon\Carbon::today());
    }

    public function subjects()
    {
        return $this->hasMany('\App\Model\Admin\subject','teacher_id');
    }

    public function per_class_payments()
    {
        return $this->hasMany('\App\Model\Admin\per_class_payment','teacher_id');
    }
    public function script_payments()
    {
        return $this->hasMany('\App\Model\Admin\script_payment','teacher_id');
    }
    public function invigilator_payments()
    {
        return $this->hasOne('\App\Model\Admin\invigilator_payment','teacher_id');
    }
    
}
