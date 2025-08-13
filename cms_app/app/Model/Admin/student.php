<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;

class student extends Model
{
    public $timestamps = true;

    public function users()
    {
        return $this->belongsTo('App\Client','client_id');
    }

    public function branches()
    {
    	return $this->belongsTo(branch::class,'branch_id');
    }

    public function batches()
    {
    	return $this->belongsTo(batch::class,'batch_id');
    }

    public function institutions()
    {
    	return $this->belongsTo(institution::class,'institution_id');
    }

    public function subjects()
    {
    	return $this->belongsTo(subject::class,'optional_id');
    }

    public function attendances()
    {
        return $this->hasMany(student_attendance::class,'student_id');
    }
    public function todays_attendance()
    {
        return $this->hasMany(student_attendance::class,'student_id')->whereDate('date', \Carbon\Carbon::today());
    }
    public function todays_present()
    {
        return $this->hasMany(student_attendance::class,'student_id')->where('attendance', 1)->whereDate('date', \Carbon\Carbon::today());
    }
    public function exams()
    {
        return $this->hasMany(student_exam_mark::class,'student_id');
    }
    public function payments()
    {
        return $this->hasMany(student_payment::class,'student_id');
    }

    public function last_payments()
    {
        return $this->hasMany(student_payment::class,'student_id')->OrderBy('id', 'desc')->limit(1);
    }

    public function online_exams()
    {
        return $this->hasMany(student_online_exam::class,'student_id');
    }

    public function latest_payments()
    {
         return $this->hasOne(student_payment::class,'student_id')->latest();
    }
}
