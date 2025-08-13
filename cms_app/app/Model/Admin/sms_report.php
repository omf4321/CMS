<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class sms_report extends Model
{    
    protected $guarded = [
        'id'
    ];
    public $timestamps = true;

    public function students()
    {
        return $this->belongsTo(student::class, 'student_id');
    }
    
}
