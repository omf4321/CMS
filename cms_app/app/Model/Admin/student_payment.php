<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;

class student_payment extends Model
{
    public $timestamps = true;

    public function students()
    {
    	return $this->belongsTo(student::class, 'student_id');
    }
    public function users()
    {
    	return $this->belongsTo('\App\Admin', 'received_id');
    }
}
