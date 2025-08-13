<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;

class teacher_payment extends Model
{
    public $timestamps = true;

    public function teachers()
    {
    	return $this->belongsTo(teacher::class, 'teacher_id');
    }
    public function users()
    {
    	return $this->belongsTo('\App\Admin', 'payer_id');
    }
}
